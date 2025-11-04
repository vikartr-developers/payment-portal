<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeBack extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'charge_backs';

  protected $fillable = [
    'merchant_name',
    'user_id',
    'request_id',
    'amount',
    'reason',
    'slip_path',
    'status',
    'date',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'date' => 'datetime',
    'amount' => 'decimal:2',
  ];

  // Relationships
  public function paymentRequest()
  {
    return $this->belongsTo(Request::class, 'request_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
