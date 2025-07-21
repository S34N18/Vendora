<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $id => $item) {
            $product = Product::find($id);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->current_price * $item['quantity']
                ];
                $total += $product->current_price * $item['quantity'];
            }
        }
        
        return view('cart.index', compact('cartItems', 'total'));
    }
    
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:10'
        ]);
        
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->get('quantity', 1);
        
        // Check stock
        if ($product->quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available'
            ], 400);
        }
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            if ($newQuantity > 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum quantity is 10'
                ], 400);
            }
            if ($product->quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ], 400);
            }
            $cart[$product->id]['quantity'] = $newQuantity;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->current_price,
                'quantity' => $quantity
            ];
        }
        
        Session::put('cart', $cart);
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart_count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$id])) {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            if ($product->quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ], 400);
            }
            
            $cart[$id]['quantity'] = $request->quantity;
            Session::put('cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_count' => array_sum(array_column($cart, 'quantity'))
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Item not found in cart'
        ], 404);
    }
    
    public function remove($id)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => array_sum(array_column($cart, 'quantity'))
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Item not found in cart'
        ], 404);
    }
    
    public function count()
    {
        $cart = Session::get('cart', []);
        return response()->json([
            'count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }
    
    public function clear()
    {
        Session::forget('cart');
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}