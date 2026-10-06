<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_swap_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('swap_no')->nullable();
            $table->unsignedBigInteger('item_inventory_id');
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('stock_out_no')->nullable();
            $table->string('set_uniq_no')->nullable();
            $table->string('uniq_serial_no')->nullable();
            $table->string('condition')->nullable();
            $table->string('from_employee_id')->nullable();
            $table->string('to_employee_id')->nullable();
            $table->unsignedBigInteger('from_branch_id')->nullable();
            $table->unsignedBigInteger('to_branch_id')->nullable();
            $table->unsignedBigInteger('from_division_id')->nullable();
            $table->unsignedBigInteger('to_division_id')->nullable();
            $table->string('swapped_by')->nullable();
            $table->timestamp('swapped_at')->nullable();
            $table->string('remarks')->nullable();
            $table->string('receive_remarks')->nullable();
            $table->string('received_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->tinyInteger('status')->default(1); // 1=pending, 2=received, 3=cancelled
            $table->timestamps();

            $table->index('swap_no');
            $table->index('item_inventory_id');
            $table->index('from_employee_id');
            $table->index('to_employee_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_swap_logs');
    }
};
