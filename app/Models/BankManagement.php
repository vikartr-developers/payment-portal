<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankManagement extends Model
{
  protected $table = 'bank_managements';

  protected $fillable = [
    'type',
    'name',
    'bank_name',
    'branch_name',
    'account_number',
    'account_holder_name',
    'ifsc_code',
    'upi_id',
    'upi_number',
    'is_merchant_upi',
    'status',
    'created_by',
    'deposit_limit',
    'daily_max_amount',
    'daily_max_transaction_count',
    'max_transaction_amount',
    // 'is_default',
  ];

  protected $casts = [
    'is_merchant_upi' => 'boolean',
  ];

  /**
   * Get the sub approvers assigned to this bank account
   */
  public function subApprovers()
  {
    return $this->belongsToMany(User::class, 'bank_sub_approvers', 'bank_management_id', 'user_id');
  }

  /**
   * Get the user who created this account
   */
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
