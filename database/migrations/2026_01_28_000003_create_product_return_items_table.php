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
        if (!Schema::hasTable('product_return_items')) {
            Schema::create('product_return_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_return_id');
                $table->unsignedBigInteger('item_inventory_id')->nullable();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_name');
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('uom', 50)->nullable();
                $table->text('damage_description')->nullable();
                $table->text('repair_notes')->nullable();
                $table->tinyInteger('status')->default(1); // 1=active,9=deleted
                $table->timestamps();

                $table->foreign('product_return_id')
                    ->references('id')
                    ->on('product_returns')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_return_items');
    }
};
