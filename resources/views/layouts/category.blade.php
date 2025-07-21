<!-- Category Header -->
    <div class="categories-header">
        <h1 class="categories-title">
            Our Categories
        </h1>
        <p class="categories-subtitle">
            Explore our diverse range of product categories and find exactly what you're looking for
        </p>
    </div>

    <!-- Categories Grid -->
    @if($categories->count() > 0)
        <div class="categories-grid">
            @foreach($categories as $category)
                <div class="category-item">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                       class="category-card">
                        
                        <!-- Category Icon/Emoji -->
                        <div class="category-icon-section">
                            <div class="category-emoji">
                                {{ $category->emoji ?? '📦' }}
                            </div>
                            <h3 class="category-name">
                                {{ $category->name }}
                            </h3>
                        </div>
                        
                        <!-- Category Info -->
                        <div class="category-info">
                            @if($category->description)
                                <p class="category-description">
                                    {{ $category->description }}
                                </p>
                            @endif
                            
                            <!-- Product Count -->
                            <div class="category-stats">
                                <span class="product-count">
                                    {{ $category->active_products_count ?? $category->products_count ?? 0 }} 
                                    {{ Str::plural('product', $category->active_products_count ?? $category->products_count ?? 0) }}
                                </span>
                                
                                <span class="view-products-link">
                                    View Products →
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Featured Categories Section -->
        @php
            $featuredCategories = $categories->filter(function($category) {
                return ($category->active_products_count ?? $category->products_count ?? 0) > 0;
            })->take(3);
        @endphp

        @if($featuredCategories->count() > 0)
            <div class="featured-categories">
                <h2 class="featured-title">
                    Popular Categories
                </h2>
                
                <div class="featured-grid">
                    @foreach($featuredCategories as $category)
                        <div class="featured-item">
                            <div class="featured-emoji">{{ $category->emoji ?? '📦' }}</div>
                            <h3 class="featured-name">{{ $category->name }}</h3>
                            <p class="featured-count">
                                {{ $category->active_products_count ?? $category->products_count ?? 0 }} products available
                            </p>
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                               class="featured-link">
                                Shop Now
                                <svg class="featured-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Category Stats -->
        <div class="category-overview">
            <h2 class="overview-title">
                Category Overview
            </h2>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number stat-blue">
                        {{ $categories->count() }}
                    </div>
                    <div class="stat-label">
                        Total Categories
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number stat-green">
                        {{ $categories->where('is_active', true)->count() }}
                    </div>
                    <div class="stat-label">
                        Active Categories
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number stat-purple">
                        {{ $categories->sum(function($cat) { return $cat->active_products_count ?? $cat->products_count ?? 0; }) }}
                    </div>
                    <div class="stat-label">
                        Total Products
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number stat-orange">
                        {{ $categories->filter(function($cat) { return ($cat->active_products_count ?? $cat->products_count ?? 0) > 0; })->count() }}
                    </div>
                    <div class="stat-label">
                        Categories with Products
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="cta-section">
            <div class="cta-card">
                <h2 class="cta-title">
                    Can't Find What You're Looking For?
                </h2>
                <p class="cta-text">
                    Browse all our products or use our search feature to find exactly what you need.@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/categories.css') }}">
@endpush

@section('content')
<div class="categories-container">
    <!-- Category Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Our Categories
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Explore our diverse range of product categories and find exactly what you're looking for
        </p>
    </div>

    <!-- Categories Grid -->
    @if($categories->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-12">
            @foreach($categories as $category)
                <div class="group">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                       class="block bg-white rounded-xl shadow-sm border hover:shadow-lg transition-all duration-300 overflow-hidden">
                        
                        <!-- Category Icon/Emoji -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-100 p-8 text-center">
                            <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">
                                {{ $category->emoji ?? '📦' }}
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                {{ $category->name }}
                            </h3>
                        </div>
                        
                        <!-- Category Info -->
                        <div class="p-6">
                            @if($category->description)
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                    {{ $category->description }}
                                </p>
                            @endif
                            
                            <!-- Product Count -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">
                                    {{ $category->active_products_count ?? $category->products_count ?? 0 }} 
                                    {{ Str::plural('product', $category->active_products_count ?? $category->products_count ?? 0) }}
                                </span>
                                
                                <span class="text-blue-600 text-sm font-medium group-hover:translate-x-1 transition-transform">
                                    View Products →
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Featured Categories Section -->
        @php
            $featuredCategories = $categories->filter(function($category) {
                return ($category->active_products_count ?? $category->products_count ?? 0) > 0;
            })->take(3);
        @endphp

        @if($featuredCategories->count() > 0)
            <div class="bg-gray-50 rounded-2xl p-8 mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    Popular Categories
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($featuredCategories as $category)
                        <div class="bg-white rounded-lg p-6 text-center hover:shadow-md transition-shadow">
                            <div class="text-3xl mb-3">{{ $category->emoji ?? '📦' }}</div>
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $category->name }}</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ $category->active_products_count ?? $category->products_count ?? 0 }} products available
                            </p>
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                                Shop Now
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Category Stats -->
        <div class="bg-white rounded-xl shadow-sm border p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                Category Overview
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-2">
                        {{ $categories->count() }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Total Categories
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2">
                        {{ $categories->where('is_active', true)->count() }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Active Categories
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 mb-2">
                        {{ $categories->sum(function($cat) { return $cat->active_products_count ?? $cat->products_count ?? 0; }) }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Total Products
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600 mb-2">
                        {{ $categories->filter(function($cat) { return ($cat->active_products_count ?? $cat->products_count ?? 0) > 0; })->count() }}
                    </div>
                    <div class="text-sm text-gray-600">
                        Categories with Products
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-12">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white">
                <h2 class="text-2xl font-bold mb-4">
                    Can't Find What You're Looking For?
                </h2>
                <p class="text-blue-100 mb-6 max-w-2xl mx-auto">
                    Browse all our products or use our search feature to find exactly what you need.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}" 
                       class="bg-white text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                        View All Products
                    </a>
                    <a href="{{ route('products.index') }}?search=" 
                       class="bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-400 transition-colors">
                        Search Products
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- No Categories Found -->
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">No categories available</h3>
            <p class="text-gray-600">Categories will appear here once they're added to the store.</p>
        </div>
    @endif
</div>
@endsection