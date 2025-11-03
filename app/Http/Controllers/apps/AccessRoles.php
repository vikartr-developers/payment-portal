<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;


use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use DataTables;

class AccessRoles extends Controller
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
  public function index()
  {
    $permissions = Permission::all()->groupBy(function ($permission) {
      return explode('-', $permission->name)[0]; // e.g. user-create → user
    });
    $roles = $this->roleService->allWithUsers();
    return view('content.apps.app-access-roles',compact('permissions','roles'));
  }



  public function store(CreateRoleRequest $request)
  {
      try {
          $roleData['name'] = $request->name;
          $roleData['display_name'] = $request->name;
          $permissions = $request->input('permissions');
          
          $role = $this->roleService->create($roleData, $permissions);
          
          if (!empty($role)) {
              return response()->json([
                  'success' => true,
                  'message' => 'Role added successfully.',
                  'redirect_url' => route('app-access-roles')
              ]);
          } else {
              return response()->json([
                  'success' => false,
                  'message' => 'Error while adding role.'
              ], 500);
          }
      } catch (\Exception $error) {
          return response()->json([
              'success' => false,
              'message' => 'Exception: ' . $error->getMessage()
          ], 500);
      }
  }

  public function update(Request $request, $id)
  {
    try {
      $role = Role::findOrFail($id);
      $role->name = $request->name;
      $role->display_name = $request->name;
      $role->save();

      $role->syncPermissions($request->permissions);

      return response()->json(['success' => true, 'message' => 'Role updated successfully.']);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $role = Role::findOrFail($id);
      $role->delete();
      return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()], 500);
    }
  }



}
