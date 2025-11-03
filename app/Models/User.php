<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  use HasApiTokens,
    HasRoles,
    HasFactory,
    Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'first_name',
    'last_name',
    'name',
    'email',
    'password',
    'contact',
    'company',
    'country',
    'role',
    'current_plan',
    'billing',
    'status',
    'avatar',
    'address_line_1',
    'address_line_2',
    'state_name',
    'zip_code',
    'country_code',
    // 2FA
    'google2fa_secret',
    'google2fa_enabled',
  ];


  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'google2fa_secret',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'google2fa_enabled' => 'boolean',
  ];
}
