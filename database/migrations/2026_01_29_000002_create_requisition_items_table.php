<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('requisition_items')) {
            Schema::create('requisition_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('requisition_id');
                $table->unsignedBigInteger('item_id')->nullable();
                $table->string('item_name');
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('uom', 50)->nullable();
                $table->text('notes')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->foreign('requisition_id')->references('id')->on('requisitions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
