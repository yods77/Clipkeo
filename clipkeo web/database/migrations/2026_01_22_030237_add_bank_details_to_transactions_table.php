<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->string('bank_code')->nullable()->after('payment_channel'); // Misal: BCA, BRI
        $table->string('account_number')->nullable()->after('bank_code');
        $table->string('account_holder')->nullable()->after('account_number');
    });
}

public function down()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropColumn(['bank_code', 'account_number', 'account_holder']);
    });
}
};
