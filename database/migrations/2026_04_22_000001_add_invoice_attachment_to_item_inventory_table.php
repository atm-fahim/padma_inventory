<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('item_inventory') && !Schema::hasColumn('item_inventory', 'invoice_attachment')) {
            Schema::table('item_inventory', function (Blueprint $table) {
                $table->string('invoice_attachment')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('item_inventory') && Schema::hasColumn('item_inventory', 'invoice_attachment')) {
            Schema::table('item_inventory', function (Blueprint $table) {
                $table->dropColumn('invoice_attachment');
            });
        }
    }
};
