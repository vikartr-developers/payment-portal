<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
  /*
  |--------------------------------------------------------------------------
  | Register Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles the registration of new users as well as their
  | validation and creation. By default this controller uses a trait to
  | provide this functionality without requiring any additional code.
  |
  */

  use RegistersUsers;

  /**
   * Where to redirect users after registration.
   *
   * @var string
   */
  protected $redirectTo = 'app/payment/requests';

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest');
  }

  /**
   * Get a validator for an incoming registration request.
   *
   * @param  array  $data
   * @return \Illuminate\Contracts\Validation\Validator
   */
  protected function validator(array $data)
  {
    return Validator::make($data, [
      'first_name' => ['required', 'string', 'max:100'],
      'last_name' => ['required', 'string', 'max:100'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password' => ['required', 'string', 'min:8', 'confirmed'],
      'contact' => ['nullable', 'string', 'max:20'],
      'company' => ['nullable', 'string', 'max:100'],
      'country' => ['nullable', 'string', 'max:100'],
    ]);
  }

  /**
   * Create a new user instance after a valid registration.
   *
   * @param  array  $data
   * @return \App\Models\User
   */
  protected function create(array $data)
  {
    // dd($data); // Debugging line to inspect the data being passed
    $user = User::create([
      'first_name' => $data['first_name'],
      'last_name' => $data['last_name'],
      'name' => $data['first_name'] . ' ' . $data['last_name'],
      'email' => $data['email'],
      'password' => Hash::make($data['password']),
      'contact' => $data['phone'] ?? null,
      'company' => $data['company'] ?? null,
      'country' => $data['country'] ?? null,
      'address_line_1' => $data['address_line1'] ?? null,
      'address_line_2' => $data['address_line2'] ?? null,
      'city' => $data['city'] ?? null,
      'state_name' => $data['state'] ?? null,
      'zip_code' => $data['zip'] ?? null,
      'country_code' => $data['country_code'] ?? null,
      'status' => '1',
    ]);

    // Assign "user" role using Spatie
    $user->assignRole('user');

    return $user;
  }
}
