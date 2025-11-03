<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;

use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleService;
use Spatie\Permission\Models\Permission;
use DataTables;

class RoleController extends Controller
{
  protected $roleService;

  public function __construct(RoleService $roleService)
  {
    $this->roleService = $roleService;
    // $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'show']]);
    // $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
    // $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
    // $this->middleware('permission:role-delete', ['only' => ['destroy']]);


    // Permission::create(['name' => 'role-list', 'guard_name' => 'web', 'module_name' => 'Roles']);
    // Permission::create(['name' => 'role-create', 'guard_name' => 'web', 'module_name' => 'Roles']);
    // Permission::create(['name' => 'role-edit', 'guard_name' => 'web', 'module_name' => 'Roles']);
    // Permission::create(['name' => 'role-delete', 'guard_name' => 'web', 'module_name' => 'Roles']);
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('content.apps.roles.list');
  }

  public function permissions_list()
  {
    $permissions = Permission::get();
    $groupedPermissions = $permissions->groupBy('module_name');
    $groupedPermissionsWithAllData = $groupedPermissions->map(function ($group) {
      return $group->all();
    });
    return $groupedPermissionsWithAllData;
  }
  public function getAll()
  {
    $roles = $this->roleService->getAllRoles();
    return DataTables::of($roles)
      ->addColumn('actions', function ($row) {
        $encryptedId = encrypt($row->id);
        // Update Button
        // $updateButton = "<a data-bs-toggle='tooltip' title='Edit' data-bs-delay='400' class='btn btn-'  href='".route('app-roles-edit',$encryptedId)."'><i class='ficon' data-feather='edit'></i></a>";
  
        $updateButton = "<a data-bs-toggle='tooltip' title='Edit' data-bs-delay='400' class='btn-sm  text-secondary'  href='" . route('app-roles-edit', $encryptedId) . "'><i class='text-secondary' data-feather='edit'></i></a>";

        // Delete Button
        // $deleteButton = "<a data-bs-toggle='tooltip' title='Delete' data-bs-delay='400' class='btn btn-danger confirm-delete' data-idos='.$encryptedId' id='confirm-color' href='".route('app-roles-delete',$encryptedId)."'><i class='ficon' data-feather='trash-2'></i></a>";
  
        $deleteButton = "<a data-bs-toggle='tooltip' title='Delete' data-bs-delay='400' class=' btn-sm  confirm-delete' data-idos='$encryptedId' id='confirm-color  href='" . route('app-roles-delete', $encryptedId) . "'><i class='text-secondory' data-feather='trash-2'></i></a>";

        return $updateButton . " " . $deleteButton;
      })
      ->addColumn('status', function ($row) {
        return $row->status
          ? '<span class="badge bg-label-primary" style="color: #000 !important;">Active</span>'
          : '<span class="badge bg-label-danger">Inactive</span>';
      })->rawColumns(['actions', 'status'])->make(true);
  }
  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $role = "";
    $page_data['page_title'] = "Role Add";
    $page_data['form_title'] = "Add New Role";
    $permissions = \Spatie\Permission\Models\Permission::all();
    $groupedPermissions = $permissions->groupBy(function ($permission) {
      return explode('-', $permission->name)[0]; // e.g., 'user' from 'user-create'
    });

    return view('content.apps.roles.create-edit', compact('page_data', 'role', 'groupedPermissions', 'permissions'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(CreateRoleRequest $request)
  {
    try {
      $roleData['name'] = $request->name;
      // dd($request->all());
      // $roleData['display_name'] = $request->display_name;
      $roleData['status'] = $request->has('status') ? 1 : 0;
      $roleData['display_name'] = $request->display_name;

      $permissions = $request->input('permissions');
      // dd($permissions);
      $role = $this->roleService->create($roleData, $permissions);
      if (!empty($role)) {
        return redirect()->route("app-roles-list")->with('success', 'Role Added Successfully');
      } else {
        return redirect()->back()->with('error', 'Error while Adding Role');
      }
    } catch (\Exception $error) {
      dd($error->getMessage());
      return redirect()->route("app-roles-list")->with('error', 'Error while editing Role');
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $encrypted_id
   * @return \Illuminate\Http\Response
   */
  public function edit($encrypted_id)
  {
    try {
      $id = decrypt($encrypted_id);
      $role = $this->roleService->getRole($id);
      $page_data['page_title'] = "Role Edit";
      $page_data['form_title'] = "Edit Role";
      // $permissions = Permission::get();
      $permissions = \Spatie\Permission\Models\Permission::all();
      $groupedPermissions = $permissions->groupBy(function ($permission) {
        return explode('-', $permission->name)[0]; // e.g., 'user' from 'user-create'
      });
      $rolePermissions = $role->permissions->pluck('id')->toArray();
      return view('/content/apps/roles/create-edit', compact('page_data', 'role', 'groupedPermissions', 'permissions', 'rolePermissions'));
    } catch (\Exception $error) {
      return redirect()->route("app-roles-list")->with('error', 'Error while editing Role');
    }
  }

  public function update(UpdateRoleRequest $request, $encrypted_id)
  {
    try {
      $id = decrypt($encrypted_id);
      $roleData['name'] = $request->name;
      $roleData['display_name'] = $request->display_name;
      $roleData['status'] = $request->has('status') ? 1 : 0; // checkbox logic

      $permissions = $request->input('permissions');

      $updated = $this->roleService->updateRole($id, $roleData, $permissions);


      if (!empty($updated)) {
        return redirect()->route("app-roles-list")->with('success', 'Role Updated Successfully');
      } else {
        return redirect()->back()->with('error', 'Error while Updating Role');
      }
    } catch (\Exception $error) {
      return redirect()->route("app-roles-list")->with('error', 'Error while editing Role');
    }
  }


  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $encrypted_id
   * @return \Illuminate\Http\Response
   */
  public function destroy($encrypted_id)
  {
    try {
      $id = decrypt($encrypted_id);
      $deleted = $this->roleService->deleteRole($id);
      if (!empty($deleted)) {
        return redirect()->route("app-roles-list")->with('success', 'Role Deleted Successfully');
      } else {
        return redirect()->back()->with('error', 'Error while Deleting Role');
      }
    } catch (\Exception $error) {
      return redirect()->route("app-roles-list")->with('error', 'Error while editing Role');
    }
  }
}
