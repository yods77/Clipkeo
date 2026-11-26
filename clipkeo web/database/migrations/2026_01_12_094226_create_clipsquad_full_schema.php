<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2. LINKED_ACCOUNTS
        Schema::create('linked_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('provider_name', ['instagram', 'tiktok', 'youtube']);
            $table->string('username');
            $table->string('provider_id')->nullable();
            $table->string('profile_url')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 3. USER_PAYOUT_METHODS
        Schema::create('user_payout_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('provider_code', 50); // BCA, DANA
            $table->string('account_number', 100);
            $table->string('account_holder_name');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // 4. BOUNTIES
        Schema::create('bounties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('source_link'); // Link video utama
            $table->enum('platform', ['tiktok', 'youtube_shorts', 'instagram_reels']);
            $table->decimal('reward_amount', 15, 2);
            $table->enum('currency', ['IDR', 'USD'])->default('IDR');
            $table->integer('winner_count')->default(1);
            $table->enum('distribution_strategy', ['winner_takes_all', 'top_3_tiered', 'equal_split'])->default('winner_takes_all');
            $table->enum('status', ['draft', 'active', 'judging', 'completed', 'canceled'])->default('draft');
            $table->json('rules')->nullable();
            
            // Kolom baru yang tadi kita bahas (Requirements & Image) saya masukkan sekalian agar lengkap
            $table->string('image_path')->nullable();
            $table->string('required_hashtags')->nullable();
            $table->string('required_mentions')->nullable();

            $table->timestamp('deadline_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. SUBMISSIONS
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bounty_id')->constrained('bounties')->onDelete('cascade');
            $table->foreignId('clipper_id')->constrained('users')->onDelete('cascade');
            $table->string('video_url', 500);
            $table->string('verification_code', 50)->nullable(); // Bisa null dulu saat submit awal
            $table->bigInteger('view_count')->default(0);
            $table->integer('rank')->nullable();
            $table->decimal('prize_amount', 15, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });

        // 6. TRANSACTIONS
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // On delete restrict: User gak bisa dihapus kalau ada transaksi
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); 
            $table->enum('type', ['deposit', 'withdrawal', 'bounty_hold', 'bounty_prize', 'bounty_refund', 'platform_fee']);
            $table->decimal('amount', 15, 2);
            $table->enum('currency', ['IDR', 'USD'])->default('IDR');
            $table->string('reference_id', 100)->nullable();
            $table->string('description');
            $table->json('meta_data')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. DEPOSITS
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('payment_gateway', 50);
            $table->string('external_ref', 100);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid', 'expired', 'failed'])->default('pending');
            $table->timestamps();
        });

        // 8. WITHDRAWAL REQUESTS
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payout_method_id')->constrained('user_payout_methods')->onDelete('restrict');
            $table->decimal('amount_requested', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0.00);
            $table->decimal('amount_final', 15, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->string('proof_attachment')->nullable();
            $table->timestamps();
        });

        // 9. REPORTS
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->string('reported_type', 50); // 'bounty', 'submission'
            $table->unsignedBigInteger('reported_id');
            $table->string('reason');
            $table->text('description');
            $table->enum('status', ['open', 'resolved', 'dismissed'])->default('open');
            $table->timestamps();
        });

        // 10. BADGES
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('icon_url');
            $table->integer('xp_req')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        // 11. USER_BADGES
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->timestamp('awarded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        // Urutan drop harus dibalik dari urutan create untuk menghindari error Foreign Key
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('bounties');
        Schema::dropIfExists('user_payout_methods');
        Schema::dropIfExists('linked_accounts');
    }
};