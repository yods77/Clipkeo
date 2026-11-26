<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password')->nullable(); // Nullable buat login Google
            $table->string('avatar_url')->nullable();
            
            // Keuangan & Reputasi
            $table->decimal('wallet_balance', 15, 2)->default(0.00);
            $table->integer('reputation_score')->default(0);
            
            $table->boolean('is_verified')->default(false);
            $table->enum('role', ['admin', 'publisher', 'clipper'])->default('clipper'); // Kita pakai 'user' (nanti dipisah logicnya jd publisher/clipper via code)
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};