<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, mixed>
   */
  public function rules()
  {
    return [
      // 'username'=>'required:string|unique:users,username',
      'email' => 'required|unique:users,email',
      'first_name' => 'required',
      'last_name' => 'required',
      'password' => 'required:string|min:8',
      // Commission percentage (visible to Approver role in the form). Accepts 0-100 with up to 2 decimals.
      'commission' => 'nullable|numeric|min:0|max:100'
    ];
  }
}
