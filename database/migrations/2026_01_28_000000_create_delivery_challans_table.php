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
        if (!Schema::hasTable('delivery_challans')) {
            Schema::create('delivery_challans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('challan_no')->unique();
                $table->date('challan_date')->nullable();
                $table->string('buyer_name')->nullable();
                $table->string('buyer_address')->nullable();
                $table->string('delivery_location')->nullable();
                $table->string('reference_no')->nullable();
                $table->tinyInteger('status')->default(1); // 1=active,9=deleted
                $table->timestamps();

                $table->index('org_id');
                $table->index('branch_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_challans');
    }
};

