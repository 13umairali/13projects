<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Authentication\Models\Institute;

class InstituteRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Institute::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Institute::query();

        return $query->paginate($perPage);
    }

    public function create(array $data): ?Institute
    {
        return Institute::create($data);
    }

    public function show(int $id): ?Institute
    {
        return Institute::find($id);
    }

    public function update(array $data, int $id): mixed
    {
        return Institute::find((int) $id)->update($data);
    }

    public function delete(int $id): void
    {
        // Find the by ID
        $institute = $this->show((int) $id);

        // A permanent, cascading delete — not the SoftDeletes-provided
        // soft delete. This institute's schema already has 164 foreign
        // key constraints with ON DELETE CASCADE pointing at institutes,
        // so the database itself removes every related row (students,
        // courses, events, forms, everything) the moment this row is
        // actually deleted. A plain delete() here would only have set
        // deleted_at and left all of that data sitting in the database
        // forever, which is not what "Delete" should mean for a Super
        // Admin action on this table.
        $institute->forceDelete();
    }
}