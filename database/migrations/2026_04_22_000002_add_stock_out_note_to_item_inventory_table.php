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
        if (Schema::hasTable('item_inventory') && !Schema::hasColumn('item_inventory', 'stock_out_note')) {
            Schema::table('item_inventory', function (Blueprint $table) {
                $table->text('stock_out_note')->nullable();
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
        if (Schema::hasTable('item_inventory') && Schema::hasColumn('item_inventory', 'stock_out_note')) {
            Schema::table('item_inventory', function (Blueprint $table) {
                $table->dropColumn('stock_out_note');
            });
        }
    }
};
