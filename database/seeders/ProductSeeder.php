<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $electronics = Category::where('slug', 'electronics')->first();
        $clothing = Category::where('slug', 'clothing')->first();
        $books = Category::where('slug', 'books')->first();
        $home = Category::where('slug', 'home')->first();

        $products = [
            [
                'name' => 'Smartphone Pro Max',
                'slug' => 'smartphone-pro-max',
                'description' => 'Latest flagship smartphone with advanced camera system and lightning-fast performance.',
                'current_price' => 999.00,
                'original_price' => 1299.00,
                'quantity' => 50,
                'category_id' => $electronics->id,
                'sku' => 'PHONE-001',
                'is_featured' => true,
            ],
            [
                'name' => 'Premium Cotton T-Shirt',
                'slug' => 'premium-cotton-t-shirt',
                'description' => 'Comfortable, breathable cotton t-shirt perfect for everyday wear. Available in multiple colors.',
                'current_price' => 29.00,
                'original_price' => 49.00,
                'quantity' => 15,
                'category_id' => $clothing->id,
                'sku' => 'SHIRT-001',
            ],
            [
                'name' => 'Web Development Guide',
                'slug' => 'web-development-guide',
                'description' => 'Complete guide to modern web development with practical examples and real-world projects.',
                'current_price' => 39.00,
                'quantity' => 100,
                'category_id' => $books->id,
                'sku' => 'BOOK-001',
            ],
            [
                'name' => 'Smart Home Speaker',
                'slug' => 'smart-home-speaker',
                'description' => 'Voice-controlled smart speaker with premium sound quality and home automation features.',
                'current_price' => 149.00,
                'quantity' => 0,
                'category_id' => $home->id,
                'sku' => 'SPEAKER-001',
            ],
            [
                'name' => 'Gaming Laptop',
                'slug' => 'gaming-laptop',
                'description' => 'High-performance gaming laptop with dedicated graphics card and ultra-fast SSD storage.',
                'current_price' => 1299.00,
                'quantity' => 25,
                'category_id' => $electronics->id,
                'sku' => 'LAPTOP-001',
            ],
            [
                'name' => 'Designer Jeans',
                'slug' => 'designer-jeans',
                'description' => 'Premium denim jeans with modern fit and sustainable materials. Perfect for any occasion.',
                'current_price' => 89.00,
                'original_price' => 120.00,
                'quantity' => 30,
                'category_id' => $clothing->id,
                'sku' => 'JEANS-001',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
