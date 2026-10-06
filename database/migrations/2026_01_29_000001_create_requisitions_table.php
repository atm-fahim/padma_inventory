<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('requisitions')) {
            Schema::create('requisitions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('requisition_no')->unique();
                $table->date('requisition_date')->nullable();
                $table->string('employee_id')->nullable();
                $table->string('employee_name')->nullable();
                $table->text('notes')->nullable();
                $table->tinyInteger('status')->default(1); // 1=draft, 2=submitted, 9=deleted
                $table->timestamps();
                $table->index(['org_id', 'branch_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
