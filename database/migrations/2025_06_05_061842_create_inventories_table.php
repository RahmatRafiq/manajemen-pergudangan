<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')
              ->constrained('warehouses')
              ->onUpdate('cascade')
              ->onDelete('cascade');
            $table->foreignId('product_id')
              ->constrained('products')
              ->onUpdate('cascade')
              ->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->integer('reserved')->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->foreignId('updated_by')
              ->nullable()
              ->constrained('users')
              ->onUpdate('cascade')
              ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['warehouse_id', 'product_id'], 'inv_wh_prod_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
