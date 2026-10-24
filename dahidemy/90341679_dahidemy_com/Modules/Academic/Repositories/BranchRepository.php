<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Authentication\Models\Branch;

class BranchRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Branch::where('institute_id', get_institute_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Branch::query();

        return $query->where('institute_id', get_institute_id())->paginate($perPage);
    }

    public function create(array $data): ?Branch
    {
        return Branch::create($data);
    }

    public function show(int $id): ?Branch
    {
        return Branch::find($id);
    }

    public function update(array $data, int $id): mixed
    {
        return Branch::find((int) $id)->update($data);
    }

    public function delete(int $id): void
    {
        // Find the branch by ID
        $branch = $this->show((int) $id);

        // Detach associated roles
        $branch->roles()->detach();

        // Delete the branch
        $branch->delete();
    }
}
