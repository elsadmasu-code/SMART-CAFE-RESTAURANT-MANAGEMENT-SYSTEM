<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('name_en');
            $table->string('name_am');
            $table->text('description_en')->nullable();
            $table->text('description_am')->nullable();
            $table->string('icon')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('sku', 50)->unique()->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->default(0.00);
            $table->unsignedSmallInteger('preparation_time_minutes')->default(10);
            $table->string('image_url')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_fasting')->default(false); // Orthodox fasting friendly
            $table->timestamps();
        });

        // 3. Product Translations (Bilingual Amharic & English)
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('locale', 5); // 'en' or 'am'
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'locale']);
        });

        // 4. Product Variants (e.g. Size: Small/Large, Shot: Single/Double)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('variant_name_en');
            $table->string('variant_name_am');
            $table->decimal('price_modifier', 12, 2)->default(0.00); // addition or subtraction to base price
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 5. Product Addons (e.g. Extra Cheese, Awaze, Honey, Extra Meat)
        Schema::create('product_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('addon_name_en');
            $table->string('addon_name_am');
            $table->decimal('extra_price', 12, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Allergens
        Schema::create('allergens', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name_en');
            $table->string('name_am');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        // 7. Product Allergens Pivot
        Schema::create('product_allergens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('allergen_id')->constrained('allergens')->cascadeOnDelete();
            $table->unique(['product_id', 'allergen_id']);
        });

        // 8. Business Hours
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $table->time('open_time')->default('06:30:00');
            $table->time('close_time')->default('22:30:00');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['branch_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
        Schema::dropIfExists('product_allergens');
        Schema::dropIfExists('allergens');
        Schema::dropIfExists('product_addons');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
