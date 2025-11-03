<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoManagement extends Model
{
  protected $table = 'crypto_managements';

  protected $fillable = [
    'wallet_address',
    'network',
    'coin',
    'status',
    'created_by',
    'updated_by',
    'is_default',
  ];
}
