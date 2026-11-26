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
    Schema::table('submissions', function (Blueprint $table) {
        // Menambah kolom path file analytics
        $table->string('analytics_path')->nullable()->after('views_count'); 
        
        // Menambah kolom untuk simpan caption
        $table->text('caption_content')->nullable()->after('video_url');
    });
}

public function down()
{
    Schema::table('submissions', function (Blueprint $table) {
        $table->dropColumn(['analytics_path', 'caption_content']);
    });
}
};
