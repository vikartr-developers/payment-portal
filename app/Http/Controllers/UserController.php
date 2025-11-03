<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  // Show the form to create a new user
  public function create()
  {
    return view('users.create');
  }

  // Handle form submission
  public function store(Request $request)
  {
    $role = $request->input('role', 'customer');

    $rules = [
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'role' => 'required|string|in:customer,admin', // Add roles as needed
    ];

    // For 'customer' role, make email, phone, password optional
    if ($role !== 'customer') {
      $rules['email'] = 'required|email|unique:users,email';
      $rules['phone'] = 'required|string|max:20';
      $rules['password'] = 'required|string|min:6|confirmed';
    } else {
      $rules['email'] = 'nullable|email|unique:users,email';
      $rules['phone'] = 'nullable|string|max:20';
      $rules['password'] = 'nullable|string|min:6|confirmed';
    }

    $validated = $request->validate($rules);

    // Create User
    $user = new User();
    $user->first_name = $validated['first_name'];
    $user->last_name = $validated['last_name'];
    $user->name = $validated['first_name'] . ' ' . $validated['last_name'];
    $user->email = now();
    $user->address_line_1 = '-';
    $user->state_name = '-';
    $user->zip_code = '-';
    $user->country_code = '-';
    $user->password = '-';
    $user->assignRole('customer');

    // $user->password = '-';
    $user->role = $validated['role'];
    if (isset($validated['email']))
      $user->email = $validated['email'];
    if (isset($validated['phone']))
      $user->contact = $validated['phone'];
    if ($validated['password']) {
      $user->password = Hash::make($validated['password']);
    }
    $user->save();

    return redirect()->route('users.create')->with('success', 'User created successfully.');
  }
}
