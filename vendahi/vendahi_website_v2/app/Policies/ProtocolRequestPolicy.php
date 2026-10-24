<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Protocol & Requests permissions:
 *  - protocol_view   : open the module, see own / desk / assigned requests
 *  - protocol_add    : submit requests (staff, or on behalf of a customer)
 *  - protocol_act    : act on requests at one's desks (receive, note, forward, …)
 *  - protocol_manage : see every request, act anywhere, configure desks
 */
class ProtocolRequestPolicy
{
    use HandlesAuthorization;

    private function has(User $user, string $slug): bool
    {
        $permission = Permission::where('name', $slug)->first();

        return $permission ? $user->hasRole($permission->roles) : false;
    }

    public function view(User $user)
    {
        return $this->has($user, 'protocol_view') || $this->has($user, 'protocol_manage');
    }

    public function create(User $user)
    {
        return $this->has($user, 'protocol_add') || $this->has($user, 'protocol_manage');
    }

    public function act(User $user)
    {
        return $this->has($user, 'protocol_act') || $this->has($user, 'protocol_manage');
    }

    public function manage(User $user)
    {
        return $this->has($user, 'protocol_manage');
    }
}
