<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the user's profile page
     */
    public function show()
    {
        $user = Auth::user();
        
        // Get user's orders if you have an Order model
        $orders = $user->orders()->latest()->paginate(10);
        
        return view('profile.show', compact('user', 'orders'));
    }

    /**
     * Show the edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        
        // Get user's orders if you have an Order model
        $orders = $user->orders()->latest()->paginate(10);
        
        return view('profile.edit', compact('user', 'orders'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'max:100'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        // Update basic info
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);

        // Update password if provided
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    }
}