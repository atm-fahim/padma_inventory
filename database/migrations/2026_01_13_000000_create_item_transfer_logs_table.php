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
        Schema::create('item_transfer_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_inventory_id');
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('stock_out_no')->nullable();   // original stock-out / invoice no
            $table->string('set_uniq_no')->nullable();    // SET_UNIQ_NO at time of transfer
            $table->string('uniq_serial_no')->nullable(); // serial at time of transfer
            $table->string('condition')->nullable();      // condition at time of transfer
            $table->string('from_employee_id')->nullable();
            $table->string('to_employee_id')->nullable();
            $table->unsignedBigInteger('from_branch_id')->nullable();
            $table->unsignedBigInteger('to_branch_id')->nullable();
            $table->string('from_device_serial_no')->nullable(); // From device serial no
            $table->string('to_device_serial_no')->nullable();   // To device serial no
            $table->unsignedBigInteger('transferred_by')->nullable(); // users.id
            $table->timestamp('transferred_at')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index('item_inventory_id');
            $table->index('stock_out_no');
            $table->index('from_employee_id');
            $table->index('to_employee_id');
            $table->index('from_branch_id');
            $table->index('to_branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transfer_logs');
    }
};

