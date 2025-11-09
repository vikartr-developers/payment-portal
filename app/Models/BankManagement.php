<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankManagement extends Model
{
  protected $table = 'bank_managements';

  protected $fillable = [
    'type',
    'bank_name',
    'branch_name',
    'account_number',
    'ifsc_code',
    'upi_id',
    'upi_number',
    'status',
    'created_by',
    'deposit_limit',
    // 'is_default',
  ];
}
