<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_log_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_log_id')->constrained('sms_logs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('status')->default('sent'); // sent | failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_log_recipients');
    }
};
