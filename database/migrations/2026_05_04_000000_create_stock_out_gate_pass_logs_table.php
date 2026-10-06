<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_out_gate_pass_logs', function (Blueprint $table) {
            $table->id();
            $table->string('stock_out_no', 191);
            $table->string('event_type', 32);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('org_id')->nullable();
            $table->timestamps();

            $table->index(['stock_out_no', 'event_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_out_gate_pass_logs');
    }
};
