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
        Schema::table('submissions', function (Blueprint $table) {
            // Menambahkan kolom linked_account_id setelah user_id
            // Nullable: Agar submission lama tidak error
            // Constrained: Terhubung ke tabel linked_accounts
            // OnDelete Set Null: Jika akun dihapus, submission tetap ada tapi id-nya null
            $table->foreignId('linked_account_id')
                  ->nullable()
                  ->after('clipper_id')
                  ->constrained('linked_accounts')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['linked_account_id']);
            $table->dropColumn('linked_account_id');
        });
    }
};
