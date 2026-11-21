<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiUsageLog;
use App\Models\Product;
use App\Models\DailyReport;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiAiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * Generate context-aware response using Google Gemini API
     */
    public function chat(
        string $userMessage,
        string $role = 'customer',
        string $locale = 'am',
        ?int $userId = null,
        ?string $conversationUuid = null
    ): array {
        // Find or create AI Conversation
        $conversation = null;
        if ($conversationUuid) {
            $conversation = AiConversation::where('conversation_uuid', $conversationUuid)->first();
        }

        if (!$conversation) {
            $conversation = AiConversation::create([
                'conversation_uuid' => $conversationUuid ?? (string) Str::uuid(),
                'user_id' => $userId,
                'role_context' => $role,
                'session_title' => mb_substr($userMessage, 0, 40) . '...',
                'locale' => $locale,
                'is_active' => true,
            ]);
        }

        // Save incoming user message
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'sender' => 'user',
            'message' => $userMessage,
            'created_at' => now(),
        ]);

        // Build domain grounding and role prompt
        $systemInstruction = $this->buildSystemInstruction($role, $locale);
        $groundingContext = $this->buildGroundingContext($role);

        $prompt = "Context and Menu Data:\n{$groundingContext}\n\nUser Question:\n{$userMessage}";

        $startTime = microtime(true);
        $replyText = '';
        $promptTokens = 0;
        $completionTokens = 0;

        // If no API key is provided, provide smart fallback responses
        if (empty($this->apiKey)) {
            $replyText = $this->generateFallbackResponse($userMessage, $role, $locale);
        } else {
            try {
                $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";
                $response = Http::timeout(15)->post($endpoint, [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemInstruction]
                        ]
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 800,
                    ]
                ]);

                $latency = microtime(true) - $startTime;

                if ($response->successful()) {
                    $json = $response->json();
                    $replyText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $promptTokens = $json['usageMetadata']['promptTokenCount'] ?? 0;
                    $completionTokens = $json['usageMetadata']['candidatesTokenCount'] ?? 0;

                    AiUsageLog::create([
                        'user_id' => $userId,
                        'model_name' => $this->model,
                        'endpoint' => 'generateContent',
                        'total_tokens' => $promptTokens + $completionTokens,
                        'latency_seconds' => $latency,
                        'status_code' => '200',
                    ]);
                } else {
                    Log::warning("Gemini API Error: " . $response->body());
                    $replyText = $this->generateFallbackResponse($userMessage, $role, $locale);
                }
            } catch (\Exception $e) {
                Log::error("Gemini API Exception: " . $e->getMessage());
                $replyText = $this->generateFallbackResponse($userMessage, $role, $locale);
            }
        }

        // Save assistant message
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'sender' => 'assistant',
            'message' => $replyText,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'created_at' => now(),
        ]);

        return [
            'reply' => $replyText,
            'conversation_uuid' => $conversation->conversation_uuid,
            'role' => $role,
            'locale' => $locale,
        ];
    }

    protected function buildSystemInstruction(string $role, string $locale): string
    {
        $base = "You are the AI Assistant for 'Fkr Cafe' located in Kombolcha, Amhara, Ethiopia. " .
            "You are polite, helpful, and strictly adhere to the business rules. " .
            "Always respond in the requested language: " . ($locale === 'am' ? 'Amharic (አማርኛ)' : 'English') . ". " .
            "Currency is always Ethiopian Birr (ETB / ብር). Never invent dishes or prices not in the provided menu data. ";

        return match ($role) {
            'manager' => $base . "You are advising the General Manager. Provide executive summaries of sales, best sellers, ingredient restock recommendations, and financial metrics.",
            'cashier' => $base . "You are helping the Cashier. Guide them on order workflows, Telebirr/CBE Birr verification steps, and daily cash reconciliation.",
            'chef' => $base . "You are assisting the Kitchen Chef. Clarify preparation times, ingredients, and recipe formulas.",
            'waiter' => $base . "You are assisting floor Waiters. Guide table service protocols and food delivery status.",
            'storekeeper' => $base . "You are assisting the Storekeeper with stock tracking, low-stock warnings, and ingredient units.",
            default => $base . "You are assisting a customer. Recommend popular Ethiopian coffee, special breakfast dishes, traditional food, juices, and pizzas. Explain allergens and ingredients politely.",
        };
    }

    protected function buildGroundingContext(string $role): string
    {
        // Ground with current available menu products
        $products = Product::with(['translations', 'category'])->where('is_available', true)->take(25)->get();
        $menuSummary = [];
        foreach ($products as $p) {
            $nameEn = $p->translations->firstWhere('locale', 'en')?->name ?? 'Item';
            $nameAm = $p->translations->firstWhere('locale', 'am')?->name ?? 'ምግብ';
            $menuSummary[] = "- {$nameEn} ({$nameAm}) | Price: {$p->price} ETB | Prep: {$p->preparation_time_minutes} mins | Fasting: " . ($p->is_fasting ? 'Yes' : 'No');
        }

        $context = "Available Menu Items:\n" . implode("\n", $menuSummary);

        if ($role === 'manager' || $role === 'storekeeper') {
            $lowStock = Ingredient::where('current_stock', '<=', 10)->take(10)->get();
            $stockSummary = [];
            foreach ($lowStock as $i) {
                $stockSummary[] = "- {$i->name_en} ({$i->name_am}): Current {$i->current_stock} {$i->unit_of_measure} (Min: {$i->minimum_stock_level})";
            }
            $context .= "\n\nLow Stock Ingredients:\n" . implode("\n", $stockSummary);
        }

        return $context;
    }

    protected function generateFallbackResponse(string $message, string $role, string $locale): string
    {
        if ($locale === 'am') {
            return match ($role) {
                'manager' => "ሰላም ስራ አስኪያጅ! ዛሬ በፍቅር ካፌ (ኮምቦልቻ) የሽያጭ እና የክምችት ሁኔታ በጥሩ ደረጃ ላይ ይገኛል። የዕለቱ ከፍተኛ ሽያጭ ያስመዘገቡት የጀበና ቡና እና ልዩ የበሬ ጥብስ ናቸው። ዝርዝር የፋይናንስ ሪፖርቱን በዳሽቦርዱ ላይ መመልከት ይችላሉ።",
                'chef' => "ሰላም ሼፍ! የወጥ ቤት ትዕዛዞች በቅደም ተከተል እየተዘጋጁ ነው። እባክዎ የምግብ ዝግጅቱ እንዳለቀ 'ተጠናቋል' የሚለውን ቁልፍ ይጫኑ።",
                default => "እንኳን ወደ ፍቅር ካፌ (ኮምቦልቻ) በደህና መጡ! ዛሬ ልዩ የጀበና ቡና፣ ልዩ የበሬ ጥብስ፣ የተጋሚኖ ሽሮ፣ ልዩ ፒዛ እና ትኩስ ስፕሪስ ጭማቂዎች ተዘጋጅተው ይጠብቁዎታል። ምን ማዘዝ ይፈልጋሉ?",
            };
        }

        return match ($role) {
            'manager' => "Hello Manager! Operations at Fkr Cafe Kombolcha are running smoothly. Top performing items today are Traditional Jebena Coffee and Special Beef Tibs. Check the dashboard for full financial breakdown.",
            'chef' => "Hello Chef! Kitchen orders are prioritized by preparation time. Please mark tickets as Ready once completed.",
            default => "Welcome to Fkr Cafe in Kombolcha! Today we recommend our Traditional Jebena Coffee, Special Beef Tibs, Shiro Tegamino, Fkr Special Pizza, and Fresh Spris Juice. How may I assist your order today?",
        };
    }
}
