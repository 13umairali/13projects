<?php

namespace App\Traits;

use Exception;

trait Authenticatable
{
    /**
     * @throws Exception
     */
    protected function getCurrentUserId(): int
    {
        if (app()->runningInConsole()) {
            return 1;
        }

        if (! isset(request()->user()->id)) {
            throw new Exception('You are not authenticated to view this.');
        }

        return (int) request()->user()->id;
    }

    /**
     * @throws Exception
     */
    protected function getCurrentInstituteId(): int
    {
        if (app()->runningInConsole()) {
            return 1;
        }

        if (! isset(request()->user()->institute_id)) {
            throw new Exception('You are not authenticated to view this.');
        }

        return (int) request()->user()->institute_id;
    }

    /**
     * @throws Exception
     */
    protected function getCurrentBranchId(): int
    {
        if (app()->runningInConsole()) {
            return 1;
        }

        if (! isset(request()->user()->branch_id)) {
            throw new Exception('You are not authenticated to view this.');
        }

        return (int) request()->user()->branch_id;
    }
}
