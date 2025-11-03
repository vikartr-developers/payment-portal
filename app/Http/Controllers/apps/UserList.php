<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\user\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\DataTables;
use App\Services\UserService;


class UserList extends Controller
{


  public function __construct(UserService $userService)
  {
    $this->userService = $userService;

    // $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show']]);
    // $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
    // $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
    // $this->middleware('permission:user-delete', ['only' => ['destroy']]);

    $permissions = [
      'user-list',
      'user-create',
      'user-edit',
      'user-delete'
    ];

    foreach ($permissions as $perm) {
      Permission::firstOrCreate(
        ['name' => $perm, 'guard_name' => 'web'],
        ['module_name' => 'Users']
      );
    }
  }

  public function index()
  {
    $roles = Role::all(); 
    return view('content.apps.app-user-list',compact('roles'));
  }



  public function getList(Request $request)
  {
    if ($request->ajax()) {
      $users = User::with('roles')->select('id', 'name as full_name', 'email', 'current_plan', 'billing', 'status', 'avatar')->get();

      // Add role name from relation
      $users->transform(function ($user) {
        $user->role = $user->roles->pluck('name')->first(); // Assumes single role
        return $user;
      });

      return DataTables::of($users)->make(true);
    }

    abort(404);
  }
  public function edit(User $user)
  {
    $role = $user->roles()->first()?->name;
    return response()->json([
      'id' => $user->id,
      'name' => $user->name,
      'email' => $user->email,
      'contact' => $user->contact,
      'company' => $user->company,
      'country' => $user->country,
      'role' => $role,
      'plan' => $user->plan,
    ]);
  }

  public function destroy(User $user)
  {
      $user->delete();
      return response()->json(['message' => 'User deleted successfully.']);
  }



  public function store(CreateUserRequest $request)
  {
    $user = User::create([
      'name' => $request->userFullname,
      'email' => $request->userEmail,
      'password' => Hash::make($request->formValidationPass),
      'contact' => $request->userContact,
      'company' => $request->companyName,
      'country' => $request->country,
      'current_plan' => $request->userPlan,
      'billing' => 0,
      'status' => 1
    ]);

    // Assign role by ID
    $roleId = $request->userRole; // should be role ID
    $role = Role::findById($roleId);
    $user->assignRole($role);

    return response()->json(['success' => true, 'message' => 'User created successfully.']);
  }


  // Update
  public function update(UpdateUserRequest $request, $encrypted_id)
  {
    $id = decrypt($encrypted_id);
    $user = User::findOrFail($id);

    $user->update([
      'name' => $request->userFullname,
      'email' => $request->userEmail,
      'contact' => $request->userContact,
      'company' => $request->companyName,
      'country' => $request->country,
      'role' => $request->userRole,
      'current_plan' => $request->userPlan,
    ]);

    return response()->json(['success' => true, 'message' => 'User updated successfully.']);
  }
}
