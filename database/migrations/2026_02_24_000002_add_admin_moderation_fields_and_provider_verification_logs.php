<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('banned_reason')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->foreignId('banned_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('providers', function (Blueprint $table): void {
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->boolean('is_suspended')->default(false);
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('suspended_until')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->foreignId('suspended_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('provider_verification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('action', 50);
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['provider_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_verification_logs');

        Schema::table('providers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropConstrainedForeignId('suspended_by');
            $table->dropColumn([
                'verified_at',
                'rejected_at',
                'rejection_reason',
                'is_suspended',
                'suspended_at',
                'suspended_until',
                'suspension_reason',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('banned_by');
            $table->dropColumn([
                'banned_reason',
                'banned_at',
            ]);
        });
    }
};
