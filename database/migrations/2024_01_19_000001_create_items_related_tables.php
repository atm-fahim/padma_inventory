<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create brands table
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->string('brand_name');
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                
                $table->index('org_id');
            });
        }

        // Create suppliers table
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->string('supplier_name');
                $table->string('contact_person')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                
                $table->index('org_id');
            });
        }

        // Create categories table
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->string('category_title')->nullable();
                $table->string('category')->nullable();
                $table->text('description')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                
                $table->index('org_id');
            });
        }

        // Create items table
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('item_title');
                $table->string('item')->nullable();
                $table->text('description')->nullable();
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('quantity', 10, 2)->default(0);
                $table->string('unit')->nullable()->default('piece');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                
                $table->index('org_id');
                $table->index('branch_id');
                $table->index('category_id');
                $table->index('brand_id');
                $table->index('supplier_id');
                
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
                $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            });
        }

        // Create item_received table
        if (!Schema::hasTable('item_received')) {
            Schema::create('item_received', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('org_id')->default(1);
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('item_id');
                $table->string('item_title')->nullable();
                $table->date('receive_date');
                $table->string('reference_no')->nullable();
                $table->decimal('qty', 10, 2)->default(0);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->enum('type', ['receive', 'return'])->default('receive');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                
                $table->index('org_id');
                $table->index('branch_id');
                $table->index('supplier_id');
                $table->index('item_id');
                
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_received');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('brands');
    }
};
