<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GeminiAiService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __construct(
        protected GeminiAiService $geminiAiService
    ) {}

    /**
     * Contextual Multilingual AI Assistant (Amharic & English)
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'locale' => 'nullable|in:am,en',
            'role_context' => 'nullable|in:customer,cashier,chef,waiter,storekeeper,manager',
            'conversation_uuid' => 'nullable|string',
        ]);

        $user = $request->user();
        $message = $request->input('message');
        $locale = $request->input('locale', 'am');

        // Infer role context if user is authenticated staff
        $role = $request->input('role_context');
        if (!$role) {
            if ($user) {
                if ($user->hasRole('manager')) $role = 'manager';
                elseif ($user->hasRole('cashier')) $role = 'cashier';
                elseif ($user->hasRole('chef')) $role = 'chef';
                elseif ($user->hasRole('waiter')) $role = 'waiter';
                elseif ($user->hasRole('storekeeper')) $role = 'storekeeper';
                else $role = 'customer';
            } else {
                $role = 'customer';
            }
        }

        $result = $this->geminiAiService->chat(
            userMessage: $message,
            role: $role,
            locale: $locale,
            userId: $user?->id,
            conversationUuid: $request->input('conversation_uuid')
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
