<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to', 20);
            $table->text('message');
            $table->string('gateway', 50)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('provider_code')->nullable();
            $table->text('provider_message')->nullable();
            $table->unsignedBigInteger('provider_message_id')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index('to');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
