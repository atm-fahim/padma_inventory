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
        if (!Schema::hasTable('product_returns')) {
            Schema::create('product_returns', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('return_no')->unique();
                $table->date('return_date')->nullable();
                $table->enum('return_type', ['damage', 'repair', 'replacement'])->default('repair');
                $table->text('reason')->nullable();
                $table->text('remarks')->nullable();
                $table->string('reference_no')->nullable();
                $table->tinyInteger('status')->default(1); // 1=pending, 2=received, 3=completed, 9=deleted
                $table->timestamps();

                $table->index('org_id');
                $table->index('branch_id');
                $table->index('supplier_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_returns');
    }
};
