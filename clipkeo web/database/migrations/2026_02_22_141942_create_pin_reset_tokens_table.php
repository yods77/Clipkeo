<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pin_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Email jadi primary key
            $table->string('token'); // Untuk simpan OTP yang di-hash
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pin_reset_tokens');
    }
};
