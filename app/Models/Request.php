<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'name',
    'mode',
    'amount',
    'payment_amount',
    'utr',
    'payment_from',
    'account_upi',
    'image',
    'assign_to',
    'status',
    'accepted_at',
    'rejected_at',
    'created_by',
    'updated_by',
    'accepted_by',
    'rejected_by'
  ];
}
