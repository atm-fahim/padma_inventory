<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quotation_approvals')) {
            Schema::create('quotation_approvals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quotation_id');
                $table->string('approval_role', 50); // admin, division_head, md, board
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->string('signature')->nullable();
                $table->text('remarks')->nullable();
                $table->tinyInteger('status')->default(1); // 1=approved, 0=rejected
                $table->timestamps();
                $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_approvals');
    }
};
