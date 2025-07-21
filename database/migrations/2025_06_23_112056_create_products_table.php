<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('current_price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->string('sku')->unique()->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable(); // For multiple images
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('attributes')->nullable(); // For size, color, etc.
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'category_id']);
            $table->index(['current_price']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
