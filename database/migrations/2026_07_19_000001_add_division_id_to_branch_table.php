<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('branch', 'division_id')) {
            Schema::table('branch', function (Blueprint $table) {
                $table->unsignedBigInteger('division_id')->nullable()->after('organization_id');
                $table->index('division_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branch', 'division_id')) {
            Schema::table('branch', function (Blueprint $table) {
                $table->dropIndex(['division_id']);
                $table->dropColumn('division_id');
            });
        }
    }
};
