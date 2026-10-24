<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\StaffCategory;
use Modules\Academic\Repositories\StaffCategoryRepository;

class StaffCategoryService
{
    public function __construct(
        private readonly StaffCategoryRepository $staffCategoryRepository
    ) {}

    public function getStaffCategories(int $perPage = 100): LengthAwarePaginator
    {
        return $this->staffCategoryRepository->paginate($perPage);
    }

    public function findStaffCategoryById(int $id): ?StaffCategory
    {
        return $this->staffCategoryRepository->show((int) $id);
    }

    public function createStaffCategory(array $data): ?StaffCategory
    {
        // institute_id/branch_id are stamped automatically by the
        // BelongsToTenant trait on the model -- no need to set them here.
        $data['sort_order'] = $data['sort_order'] ?? 100;

        return $this->staffCategoryRepository->create($data);
    }

    public function updateStaffCategory(array $data, int $id): mixed
    {
        return $this->staffCategoryRepository->update($data, $id);
    }

    public function deleteStaffCategoryById(int $id): int
    {
        return $this->staffCategoryRepository->delete($id);
    }
}
