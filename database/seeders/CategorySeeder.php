<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use App\Models\Category;

class CategorySeeder extends Seeder
{
public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'emoji' => '📱',
                'description' => 'Latest gadgets and electronic devices'
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'emoji' => '👕',
                'description' => 'Fashion and apparel for all occasions'
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'emoji' => '📚',
                'description' => 'Educational and entertainment books'
            ],
            [
                'name' => 'Home',
                'slug' => 'home',
                'emoji' => '🏠',
                'description' => 'Home and garden essentials'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

