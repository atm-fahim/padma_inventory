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
        if (!Schema::hasTable('quotation_items')) {
            Schema::create('quotation_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quotation_id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_name');
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('uom', 50)->nullable();
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('vat', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();

                $table->foreign('quotation_id')
                    ->references('id')
                    ->on('quotations')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
