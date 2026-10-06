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
        if (!Schema::hasTable('quotation_approvals')) {
            Schema::create('quotation_approvals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quotation_id');
                $table->enum('approval_level', ['admin', 'division_head', 'md', 'board'])->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->string('approver_name')->nullable();
                $table->string('approver_designation')->nullable();
                $table->text('comments')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->foreign('quotation_id')
                    ->references('id')
                    ->on('quotations')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_approvals');
    }
};
