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
        if (!Schema::hasTable('delivery_challan_items')) {
            Schema::create('delivery_challan_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delivery_challan_id');
                $table->unsignedBigInteger('item_inventory_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_name');
                $table->string('description')->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('uom', 50)->nullable();
                $table->string('warranty')->nullable();
                $table->tinyInteger('status')->default(1); // 1=active,9=deleted
                $table->timestamps();

                $table->foreign('delivery_challan_id')
                    ->references('id')
                    ->on('delivery_challans')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_challan_items');
    }
};

