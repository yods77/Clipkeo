<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('submissions', 'video_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                // Social platform IDs may be numeric or alphanumeric. Keep this
                // nullable so existing submissions remain valid.
                $table->string('video_id', 100)
                    ->nullable()
                    ->after('video_url');

                $table->index('video_id', 'submissions_video_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('submissions', 'video_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropIndex('submissions_video_id_index');
                $table->dropColumn('video_id');
            });
        }
    }
};
