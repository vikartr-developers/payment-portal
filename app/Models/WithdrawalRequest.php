<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class WithdrawalRequest extends Model
{
  use SoftDeletes;

  protected $table = 'withdrawal_requests';

  protected $fillable = [
    'trans_id',
    'account_holder_name',
    'account_number',
    'confirm_account_number',
    'branch_name',
    'ifsc_code',
    'amount',
    'status',
    'approver_status',
    'screenshot',
    'created_by',
    'updated_by',
    'deleted_by',
  ];


  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

}
