<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')
              ->nullable()
              ->constrained('categories')
              ->onUpdate('cascade')
              ->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('unit')->default('pcs');
            $table->foreignId('created_by')
              ->nullable()
              ->constrained('users')
              ->onUpdate('cascade')
              ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
