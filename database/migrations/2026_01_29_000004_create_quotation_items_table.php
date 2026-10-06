<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quotation_items')) {
            Schema::create('quotation_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quotation_id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_name');
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('uom', 50)->nullable();
                $table->decimal('unit_price', 14, 2)->default(0);
                $table->decimal('discount_amount', 14, 2)->default(0);
                $table->decimal('vat_rate', 8, 2)->default(0);
                $table->decimal('vat_amount', 14, 2)->default(0);
                $table->decimal('tax_rate', 8, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('line_total', 14, 2)->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
