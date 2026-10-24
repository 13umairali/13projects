<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\TenantLimitsService;
use App\Support\PlanPermissionMap;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PermissionsController extends BaseController
{
    // ----------- GET ALL Roles --------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Role::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = strtolower((string) $request->input('SortType')) === 'asc' ? 'asc' : 'desc';
        $helpers = new helpers;

        $roles = Role::where('deleted_at', '=', null)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('description', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $roles->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }
        $roles = $roles->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'roles' => $roles,
            'totalRows' => $totalRows,
        ]);
    }

    // ----------- Store new Role --------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Role::class);

        try {
            request()->validate([
                'role.name' => 'required',
            ]);

            // A role may only hold permissions the tenant's plan includes.
            $this->rejectPermissionsOutsidePlan((array) $request->permissions);

            \DB::transaction(function () use ($request) {

                // -- Create New Role
                $Role = new Role;
                $Role->name = $request['role']['name'];
                $Role->label = $request['role']['name'];
                $Role->status = 0;
                $Role->description = $request['role']['description'];
                $Role->save();

                $role = Role::findOrFail($Role->id);
                $role->permissions()->detach();
                $permissions = $request->permissions;

                foreach ($permissions as $permission_slug) {
                    $perm = Permission::firstOrCreate(['name' => $permission_slug]);
                    $data[] = $perm->id;
                }

                $role->permissions()->attach($data);

            }, 10);

            return response()->json(['success' => true]);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => 422,
                'msg' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

    }

    // ------------ function show -----------\\

    public function show($id)
    {
        //

    }

    // ----------- Update Role --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Role::class);

        try {
            request()->validate([
                'role.name' => 'required',
            ]);

            // A role may only hold permissions the tenant's plan includes.
            $this->rejectPermissionsOutsidePlan((array) $request->permissions);

            \DB::transaction(function () use ($request, $id) {

                Role::whereId($id)->update($request['role']);

                $role = Role::findOrFail($id);
                $role->permissions()->detach();
                $permissions = $request->permissions;

                foreach ($permissions as $permission_slug) {

                    // get the permission object by name
                    $perm = Permission::firstOrCreate(['name' => $permission_slug]);
                    $data[] = $perm->id;
                }

                $role->permissions()->attach($data);

            }, 10);

            return response()->json(['success' => true]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'msg' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

    }

    // ----------- Delete Role --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Role::class);

        Role::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true]);
    }

    // -------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'delete', Role::class);

        $selectedIds = $request->selectedIds;
        foreach ($selectedIds as $role_id) {
            Role::whereId($role_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    // ----------- GET ALL Roles without paginate --------------\\

    public function getRoleswithoutpaginate()
    {
        $roles = Role::where('deleted_at', null)->get(['id', 'name']);

        return response()->json($roles);
    }

    // ------------- Show Form Edit Permissions -----------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Role::class);

        if ($id != '1') {
            $Role = Role::with('permissions')->where('deleted_at', '=', null)->findOrFail($id);
            if ($Role) {
                $item['name'] = $Role->name;
                $item['description'] = $Role->description;
                $data = [];
                if ($Role) {
                    foreach ($Role->permissions as $permission) {
                        $data[] = $permission->name;
                    }
                }
            }

            return response()->json([
                'permissions' => $data,
                'role' => $item,
            ]);

        } else {
            return response()->json([
                'success' => false,
            ], 401);
        }
    }
    // ------------ plan gating -----------\

    /**
     * GET roles/available_permissions — which permission slugs the tenant's
     * plan does NOT include, so the role builder can hide them. Core
     * permissions (not tied to any plan feature) are never locked.
     */
    public function availablePermissions(Request $request, TenantLimitsService $limits)
    {
        $this->authorizeForUser($request->user('api'), 'view', Role::class);

        $plan = $limits->getActivePlan();
        $features = [];
        foreach (\App\Models\Central\Plan::AVAILABLE_FEATURES as $key => $meta) {
            $features[$key] = ['label' => $meta['label'], 'enabled' => $plan ? $plan->hasFeature($key) : false];
        }

        return response()->json([
            'has_plan' => (bool) $plan,
            'plan_name' => $plan ? $plan->name : null,
            'features' => $features,
            'locked' => array_values(PlanPermissionMap::lockedFor($plan)),
            'map' => PlanPermissionMap::MAP,
        ]);
    }

    /** 422 when a submitted slug belongs to a feature the plan does not include. */
    private function rejectPermissionsOutsidePlan(array $slugs)
    {
        $plan = app(TenantLimitsService::class)->getActivePlan();
        $disallowed = PlanPermissionMap::disallowed($plan, $slugs);
        if ($disallowed) {
            $labels = implode(', ', array_unique(array_values($disallowed)));
            throw ValidationException::withMessages([
                'permissions' => ["These permissions are not included in your plan: {$labels}. Ask your network admin to upgrade the plan."],
            ]);
        }
    }
}
