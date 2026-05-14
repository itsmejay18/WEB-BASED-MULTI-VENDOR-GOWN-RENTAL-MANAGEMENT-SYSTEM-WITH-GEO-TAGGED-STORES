<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('parent_id');
            $table->index('is_active');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 120)->unique();
            $table->enum('type', ['occasion', 'style', 'color', 'material', 'other'])->default('other');
            $table->timestamps();

            $table->index('type');
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->string('slug', 280)->unique();
            $table->text('description')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('designer', 100)->nullable();
            $table->enum('condition_rating', ['new', 'like_new', 'good', 'fair'])->default('good');
            $table->text('cleaning_policy')->nullable();
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->decimal('late_fee_per_day', 10, 2)->default(0);
            $table->unsignedInteger('total_rentals')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('provider_id');
            $table->index('category_id');
            $table->index('brand');
            $table->index('is_active');
        });

        Schema::create('item_tags', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['item_id', 'tag_id']);
        });

        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 100)->nullable()->unique();
            $table->string('size_label', 50);
            $table->string('color', 50)->nullable();
            $table->string('material', 100)->nullable();
            $table->decimal('chest_cm', 5, 2)->nullable();
            $table->decimal('waist_cm', 5, 2)->nullable();
            $table->decimal('length_cm', 5, 2)->nullable();
            $table->decimal('inseam_cm', 5, 2)->nullable();
            $table->decimal('shoulder_cm', 5, 2)->nullable();
            $table->unsignedInteger('quantity_available')->default(1);
            $table->text('additional_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('item_id');
            $table->index('size_label');
            $table->index('quantity_available');
        });

        Schema::create('item_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('item_variants')->nullOnDelete();
            $table->string('photo_url');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('item_id');
            $table->index('is_primary');
        });

        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('duration_days');
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['item_id', 'duration_days']);
            $table->index('is_active');
        });

        Schema::create('availability_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('item_variants')->cascadeOnDelete();
            $table->date('available_date');
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['variant_id', 'available_date']);
            $table->index('available_date');
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_calendar');
        Schema::dropIfExists('pricing_tiers');
        Schema::dropIfExists('item_photos');
        Schema::dropIfExists('item_variants');
        Schema::dropIfExists('item_tags');
        Schema::dropIfExists('items');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
