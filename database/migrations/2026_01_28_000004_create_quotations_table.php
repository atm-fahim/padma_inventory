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
        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('quotation_no')->unique();
                $table->date('quotation_date')->nullable();
                $table->string('employee_id')->nullable();
                $table->string('employee_name')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('vat_amount', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->text('note')->nullable();
                $table->enum('status', ['draft', 'pending', 'admin_approved', 'division_head_approved', 'md_approved', 'board_approved', 'rejected'])->default('draft');
                $table->tinyInteger('active_status')->default(1); // 1=active,9=deleted
                $table->timestamps();

                $table->index('org_id');
                $table->index('branch_id');
                $table->index('supplier_id');
                $table->index('quotation_no');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
