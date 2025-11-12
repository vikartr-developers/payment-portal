<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Models\BankManagement;
use App\Models\CryptoManagement;
use Spatie\Permission\Models\Permission;

use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
  protected UserService $userService;
  protected RoleService $roleService;

  public function __construct(UserService $userService, RoleService $roleService)
  {
    $this->userService = $userService;
    $this->roleService = $roleService;
    // $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show']]);
    // $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
    // $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
    // $this->middleware('permission:user-delete', ['only' => ['destroy']]);



    // Permission::create(['name' => 'user-list', 'guard_name' => 'web', 'module_name' => 'Users']);
    // Permission::create(['name' => 'user-create', 'guard_name' => 'web', 'module_name' => 'Users']);
    // Permission::create(['name' => 'user-edit', 'guard_name' => 'web', 'module_name' => 'Users']);
    // Permission::create(['name' => 'user-delete', 'guard_name' => 'web', 'module_name' => 'Users']);



    // Permission::create(['name' => 'product-list', 'guard_name' => 'web', 'module_name' => 'Products']);
    // Permission::create(['name' => 'product-create', 'guard_name' => 'web', 'module_name' => 'Products']);
    // Permission::create(['name' => 'product-edit', 'guard_name' => 'web', 'module_name' => 'Products']);
    // Permission::create(['name' => 'product-delete', 'guard_name' => 'web', 'module_name' => 'Products']);


    // Permission::create(['name' => 'category-list', 'guard_name' => 'web', 'module_name' => 'Categories']);
    // Permission::create(['name' => 'category-create', 'guard_name' => 'web', 'module_name' => 'Categories']);
    // Permission::create(['name' => 'category-edit', 'guard_name' => 'web', 'module_name' => 'Categories']);
    // Permission::create(['name' => 'category-delete', 'guard_name' => 'web', 'module_name' => 'Categories']);

    // Permission::create(['name' => 'bank-account-list', 'guard_name' => 'web', 'module_name' => 'bank-accounts']);
    // Permission::create(['name' => 'bank-account-create', 'guard_name' => 'web', 'module_name' => 'bank-accounts']);
    // Permission::create(['name' => 'bank-account-edit', 'guard_name' => 'web', 'module_name' => 'bank-accounts']);
    // Permission::create(['name' => 'bank-account-delete', 'guard_name' => 'web', 'module_name' => 'bank-accounts']);

    // Permission::create(['name' => 'role-list', 'guard_name' => 'web', 'module_name' => 'Roles']);
    // Permission::create(['name' => 'role-create', 'guard_name' => 'web', 'module_name' => 'Roles']);
    // Permission::create(['name' => 'role-edit', 'guard_name' => 'web', 'module_name' => 'Roles']);
    // Permission::create(['name' => 'role-delete', 'guard_name' => 'web', 'module_name' => 'Roles']);

  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function index()
  {
    // $data['total_user'] = User::where('status', true)->count();
    // $data['customers'] = User::role('User')->count();
    // $data['customers'] = 10;
    $data['customers'] = User::role('SubApprover')->count();
    // dd(auth()->user()->getRoleNames());
    // $data['admin_count'] = User::leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
    //     ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')->where('roles.display_name', 'Admin')->count();
    // $data['admin_count'] = User::role('Super Admin')->count();
    $data['admin_count'] = 1;
    return view('content.apps.user.list', compact('data'));
  }


  public function getAll()
  {
    $users = $this->userService->getAllUser();
    // dd($users->role);
    return DataTables::of($users)->addColumn('full_name', function ($row) {
      return $row->first_name;
    })->addColumn('full_name', function ($row) {
      return $row->first_name;
    })
      ->addColumn('role_name', function ($row) {
        return head($row->getRoleNames());
      })->addColumn('actions', function ($row) {
        $encryptedId = encrypt($row->id);
        // Update Button
        // $updateButton = "<a data-bs-toggle='tooltip' title='Edit' data-bs-delay='400' class='btn btn-warning'  href='" . route('app-users-edit', $encryptedId) . "'><i data-feather='edit'></i></a>";
  
        $updateButton = "<a data-bs-toggle='tooltip' title='Edit' data-bs-delay='400' class='btn-sm border-warning'  href='" . route('app-users-edit', $encryptedId) . "'><i class='text-warning' data-feather='edit'></i></a>";

        // Delete Button
        // $deleteButton = "<a data-bs-toggle='tooltip' title='Delete' data-bs-delay='400' class='btn btn-danger confirm-delete' data-idos='.$encryptedId' id='confirm-color  href='" . route('app-users-destroy', $encryptedId) . "'><i data-feather='trash-2'></i></a>";
  
        // $deleteButton = "<a data-bs-toggle='tooltip' title='Delete' data-bs-delay='400' class=' btn-sm border-danger confirm-delete' data-idos='$encryptedId' id='confirm-color  href='" . route('app-users-destroy', $encryptedId) . "'><i class='text-danger' data-feather='trash-2'></i></a>";
  
        return $updateButton;
      })->rawColumns(['actions'])->make(true);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function create()
  {
    $page_data['page_title'] = "User";
    $page_data['form_title'] = "Add New User";
    $user = '';
    $userslist = $this->userService->getAllUser();
    $roles = $this->roleService->getAllRoles();

    $data['reports_to'] = User::all();
    return view('.content.apps.user.create-edit', compact('page_data', 'user', 'userslist', 'roles', 'data'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(CreateUserRequest $request)
  {
    try {
      $userData['username'] = $request->get('username');
      $userData['name'] = $request->get('first_name') . ' ' . $request->get('last_name');
      $userData['address_line_1'] = $request->get('first_name') . ' ' . $request->get('last_name');

      $userData['first_name'] = $request->get('first_name');
      $userData['last_name'] = $request->get('last_name');
      $userData['email'] = $request->get('email');
      $userData['contact'] = $request->get('phone_no');
      $userData['password'] = Hash::make($request->get('password'));
      $userData['from_slab'] = $request->get('from_slab');
      $userData['to_slab'] = $request->get('to_slab');

      $userData['address'] = $request->get('address');
      // Only allow setting commission if currently logged-in user has role 'Approver'
      if (auth()->user() && auth()->user()->hasRole('Approver')) {
        $userData['commission'] = $request->get('commission');
      }
      if (auth()->user() && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin'))) {
        $userData['status'] = $request->get('status') == 'on' ? true : false;
      } else {
        $userData['status'] = true;
      }
      $user = $this->userService->create($userData);

      // Determine role assignment: only Admin/Super Admin can set arbitrary role.
      if (auth()->user() && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin'))) {
        $role = Role::find($request->get('role'));
      } else {
        // default to SubApprover
        $role = Role::where('name', 'SubApprover')->first();
      }

      if ($role) {
        $user->assignRole($role);
      }

      if (!empty($user)) {
        return redirect()->route("app-users-list")->with('success', 'User Added Successfully');
      } else {
        return redirect()->back()->with('error', 'Error while Adding User');
      }
    } catch (\Exception $error) {
      dd($error->getMessage());
      return redirect()->route("app-users-list")->with('error', 'Error while adding User');
    }
  }

  public function profile()
  {
    $bankAccounts = auth()->user()->bankAccounts; // Assuming user has a relation to bank accounts
    // dd($bankAccounts);
    $bankAccounts = BankManagement::where('created_by', auth()->user()->id)->get();
    $cryptoAccounts = CryptoManagement::where('created_by', auth()->user()->id)->get();
    // dd($bankAccounts);
    return view('content.pages.pages-account-settings-account', compact('bankAccounts', 'cryptoAccounts'));
  }
  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
   */
  public function edit($encrypted_id)
  {
    try {
      $id = decrypt($encrypted_id);
      $user = $this->userService->getUser($id);
      $page_data['page_title'] = "User";
      $page_data['form_title'] = "Edit User";

      $userslist = $this->userService->getAllUser();
      $roles = $this->roleService->getAllRoles();
      $user->role = $user->getRoleNames()[0];

      $data['reports_to'] = User::all();
      return view('/content/apps/user/create-edit', compact('page_data', 'user', 'data', 'roles', 'userslist'));
    } catch (\Exception $error) {
      return redirect()->route("app-users-list")->with('error', 'Error while editing User');
    }
  }

  /**
   * Update the specified resource in storage.
   *
   * @param UpdateUserRequest $request
   * @param $encrypted_id
   * @return \Illuminate\Http\RedirectResponse
   */

  public function updateProfile(UpdateUserProfileRequest $request, $encrypted_id)
  {
    try {
      // dd($request->all());
      $id = $encrypted_id;
      // $userData['username'] = $request->get('username');
      $userData['first_name'] = $request->get('first_name');
      $userData['last_name'] = $request->get('last_name');
      $userData['address_line_1'] = $request->get('first_name') . ' ' . $request->get('last_name');
      $userData['email'] = $request->get('email');
      $userData['contact'] = $request->get('phone_no');
      $user = User::where('id', $id)->first();
      $updated = $this->userService->updateUser($id, $userData);
      if (!empty($updated)) {

        return redirect()->back()->with('success', 'Profile updated successfully');
      } else {

        return redirect()->back()->with('error', 'Error while Updating User');
      }
    } catch (\Exception $error) {
      dd($error->getMessage());
      return redirect()->back()->with('error', 'Error while editing User');
    }
  }
  public function update(UpdateUserRequest $request, $encrypted_id)
  {
    try {
      // dd($request->all());
      $id = decrypt($encrypted_id);
      // $userData['username'] = $request->get('username');
      $userData['first_name'] = $request->get('first_name');
      $userData['last_name'] = $request->get('last_name');
      $userData['email'] = $request->get('email');
      $userData['contact'] = $request->get('phone_no');
      // $userData['address_line_1'] = $request->get('address_line_1');
      $userData['address_line_2'] = $request->get('address_line_2');
      // $userData['city'] = $request->get('city');
      $userData['address_line_1'] = $request->get('first_name') . ' ' . $request->get('last_name');

      $userData['state_name'] = $request->get('state_name');
      $userData['zip_code'] = $request->get('zip_code');
      $userData['from_slab'] = $request->get('from_slab');
      $userData['to_slab'] = $request->get('to_slab');
      // Only allow Approver to set commission on update
      if (auth()->user() && auth()->user()->hasRole('Approver')) {
        $userData['commission'] = $request->get('commission');
      }
      if ($request->get('password') != null && $request->get('password') != '') {
        $userData['password'] = Hash::make($request->get('password'));
      }
      // $userData['dob'] = $request->get('dob');
      // $userData['address'] = $request->get('address');
      // $userData['report_to'] = $request->get('report_to');
      // Only Admin or Super Admin can update the status flag
      if (auth()->user() && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin'))) {
        $userData['status'] = $request->get('status') == 'on' ? true : false;
      }
      $updated = $this->userService->updateUser($id, $userData);
      $user = User::where('id', $id)->first();
      // Only Admin/Super Admin can change user role
      if (auth()->user() && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin'))) {
        $role = Role::find($request->get('role'));
        if ($role) {
          // remove current role and assign the new one
          $currentRoles = $user->getRoleNames();
          if (!empty($currentRoles)) {
            $user->removeRole($currentRoles[0]);
          }
          $user->assignRole($role);
        }
      }
      if (!empty($updated)) {
        return redirect()->route("app-users-list")->with('success', 'User Updated Successfully');
      } else {
        return redirect()->back()->with('error', 'Error while Updating User');
      }
    } catch (\Exception $error) {
      dd($error->getMessage());
      return redirect()->route("app-users-list")->with('error', 'Error while editing User');
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param $encrypted_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy($encrypted_id)
  {
    try {
      $id = decrypt($encrypted_id);
      $deleted = $this->userService->deleteUser($id);
      if (!empty($deleted)) {
        return redirect()->route("app-users-list")->with('success', 'Users Deleted Successfully');
      } else {
        return redirect()->back()->with('error', 'Error while Deleting Users');
      }
    } catch (\Exception $error) {
      return redirect()->route("app-users-list")->with('error', 'Error while editing Users');
    }
  }


  public function siteUserIndex()
  {
    $data['admin_users'] = User::whereNotIn('id', User::role('User')->pluck('id'))->count();
    $data['customers'] = User::role('User')->count();
    return view('content.apps.user.site_user_list', compact('data'));
  }

  public function getAllSiteUsers()
  {
    $users = $this->userService->getAllSiteUser();
    return DataTables::of($users)
      ->addColumn('name', function ($row) {
        return $row->name ?? ($row->first_name . ' ' . $row->last_name);
      })
      ->addColumn('status', function ($row) {
        if ($row->status == 1 || $row->status == 'active') {
          return '<span class="badge bg-success">Active</span>';
        } else {
          return '<span class="badge bg-danger">Inactive</span>';
        }
      })
      ->addColumn('updated_at', function ($row) {
        return $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : '-';
      })
      ->addColumn('actions', function ($row) {
        $encryptedId = encrypt($row->id);

        // Edit Button
        $editButton = "<a data-bs-toggle='tooltip' title='Edit' class='btn btn-sm btn-primary me-1' href='" . route('app-users-edit', $encryptedId) . "'><i class='ti ti-edit'></i></a>";

        // Disable/Enable Button
        $statusText = ($row->status == 1 || $row->status == 'active') ? 'Disable' : 'Enable';
        $statusIcon = ($row->status == 1 || $row->status == 'active') ? 'ban' : 'check';
        $statusClass = ($row->status == 1 || $row->status == 'active') ? 'warning' : 'success';
        $toggleUrl = route('app-users-toggle-status', $encryptedId);

        $disableButton = "<button data-bs-toggle='tooltip' title='" . $statusText . "' class='btn btn-sm btn-" . $statusClass . " toggle-status' data-id='" . $row->id . "' data-url='" . $toggleUrl . "'><i class='ti ti-" . $statusIcon . "'></i></button>";

        return $editButton . $disableButton;
      })
      ->rawColumns(['status', 'actions'])
      ->make(true);
  }

  /**
   * Toggle user status (active/inactive)
   */
  public function toggleStatus($encrypted_id)
  {
    try {
      $id = decrypt($encrypted_id);
      $user = User::findOrFail($id);

      // Toggle status
      if ($user->status == 1 || $user->status == 'active') {
        $user->status = 0;
        $message = 'User disabled successfully';
      } else {
        $user->status = 1;
        $message = 'User enabled successfully';
      }

      $user->save();

      return response()->json([
        'success' => true,
        'message' => $message,
        'status' => $user->status
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update user status'
      ], 500);
    }
  }
}
