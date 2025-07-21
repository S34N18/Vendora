@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
            <a href="{{ route('profile.edit') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                Edit Profile
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Profile Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <p class="text-gray-900 font-medium">{{ $user->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <p class="text-gray-900 font-medium">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <p class="text-gray-900 font-medium">{{ $user->city ?? 'Not provided' }}</p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <p class="text-gray-900 font-medium">{{ $user->address ?? 'Not provided' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <p class="text-gray-900 font-medium">{{ $user->postal_code ?? 'Not provided' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <p class="text-gray-900 font-medium">{{ $user->country ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Summary & Recent Orders -->
            <div class="space-y-6">
                <!-- Profile Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Profile Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Member since:</span>
                            <span class="font-medium">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Orders:</span>
                            <span class="font-medium">{{ $orders->total() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email Status:</span>
                            <span class="font-medium {{ $user->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                {{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                @if($orders->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Orders</h3>
                    <div class="space-y-3">
                        @foreach($orders->take(3) as $order)
                        <div class="border-b pb-3 last:border-b-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium">#{{ $order->id }}</p>
                                    <p class="text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium">${{ number_format($order->total, 2) }}</p>
                                    <p class="text-sm text-gray-600">{{ ucfirst($order->status) }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('orders.index') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All Orders →
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection