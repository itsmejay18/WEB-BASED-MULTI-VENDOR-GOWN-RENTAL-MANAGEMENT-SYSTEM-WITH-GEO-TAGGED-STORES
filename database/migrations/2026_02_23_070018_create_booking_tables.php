<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 50)->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('provider_id')->constrained();
            $table->foreignId('variant_id')->constrained('item_variants');
            $table->foreignId('pickup_location_id')->nullable()->constrained('provider_locations')->nullOnDelete();
            $table->foreignId('delivery_address_id')->nullable()->constrained('user_addresses')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_days')->storedAs('DATEDIFF(end_date, start_date) + 1');
            $table->decimal('rental_price', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'ready_for_pickup',
                'picked_up',
                'returned',
                'completed',
                'disputed',
            ])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'partially_refunded'])->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->text('special_requests')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('cancelled_by', ['user', 'provider', 'system'])->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('provider_id');
            $table->index('variant_id');
            $table->index(['start_date', 'end_date']);
            $table->index('status');
            $table->index('payment_status');
            $table->index('booking_number');
        });

        Schema::create('booking_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('status', 50);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('booking_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('user_id')->constrained('users');
            $table->string('payment_intent_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('PHP');
            $table->string('payment_method', 50)->nullable();
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->text('refund_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('user_id');
            $table->index('status');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('provider_id')->constrained('providers');
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->unsignedTinyInteger('item_condition_rating')->nullable();
            $table->unsignedTinyInteger('communication_rating')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('provider_id');
            $table->index('item_id');
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_timeline');
        Schema::dropIfExists('bookings');
    }
};
