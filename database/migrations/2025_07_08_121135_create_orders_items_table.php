<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
// This migration creates the order_items table, which is used to store items in an order.
// Each item is linked to an order and a product, with fields for quantity, price,
// and total price of the item. The foreign keys ensure that if an order or product is
// deleted, the corresponding items are also removed from the order_items table.
// The timestamps will automatically manage created_at and updated_at fields for each item.
// This structure allows for efficient management of order items in an e-commerce application.