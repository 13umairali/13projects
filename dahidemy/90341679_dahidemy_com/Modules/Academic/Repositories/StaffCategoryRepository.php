<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\StaffCategory;

class StaffCategoryRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return StaffCategory::orderBy('sort_order')->orderBy('name')->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        return StaffCategory::withCount('staffs')->orderBy('sort_order')->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): ?StaffCategory
    {
        return StaffCategory::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return StaffCategory::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return StaffCategory::destroy($id);
    }

    public function show(int $id): ?StaffCategory
    {
        return StaffCategory::find($id);
    }
}
