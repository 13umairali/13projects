<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

/**
 * Every institute can create its own custom roles (roles.institute_id is
 * nullable, but real - institutes are NOT limited to a fixed built-in
 * list). Without this, the SaaS admin configuring referral rewards would
 * be typing role names blind, with no way to know a role like "account2"
 * even exists until someone with it happens to make a referral.
 */
class RoleOptionController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        return $this->responseSuccess($roles, 'Role names fetched successfully.');
    }
}