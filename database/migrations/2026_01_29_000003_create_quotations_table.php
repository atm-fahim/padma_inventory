<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('requisition_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('quotation_no')->unique();
                $table->date('quotation_date')->nullable();
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('discount_amount', 14, 2)->default(0);
                $table->decimal('vat_amount', 14, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('total', 14, 2)->default(0);
                $table->text('note')->nullable();
                $table->tinyInteger('status')->default(1); // 1=draft, 2=submitted, 3=approved_admin, 4=approved_division_head, 5=approved_md, 6=approved_board, 9=rejected
                $table->timestamps();
                $table->index(['org_id', 'supplier_id']);
                $table->foreign('requisition_id')->references('id')->on('requisitions')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
