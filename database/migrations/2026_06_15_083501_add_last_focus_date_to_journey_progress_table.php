<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('journey_progress', 'last_focus_date')) {
            Schema::table('journey_progress', function (Blueprint $table) {
                $table->date('last_focus_date')->nullable()->after('level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('journey_progress', 'last_focus_date')) {
            Schema::table('journey_progress', function (Blueprint $table) {
                $table->dropColumn('last_focus_date');
            });
        }
    }
};
