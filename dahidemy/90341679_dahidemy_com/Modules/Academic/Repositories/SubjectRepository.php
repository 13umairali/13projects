<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Subject;

class SubjectRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Subject::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Subject::query();

        // A subject whose parent class was soft-deleted is excluded here
        // too - this repository is the actual data source behind
        // dropdowns/lists elsewhere in the app, and previously returned
        // such subjects since it never checked the parent class's
        // deletion status at all.
        return $query->select('subjects.*')
            ->join('classes', 'classes.id', '=', 'subjects.class_id')
            ->whereNull('classes.deleted_at')
            ->orderBy('subjects.id', 'asc')
            ->where('subjects.institute_id', get_institute_id())
            ->where('subjects.branch_id', get_branch_id())
            ->paginate($perPage);
    }

    public function create(array $data): ?Subject
    {
        return Subject::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Subject::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Subject::destroy($id);
    }

    public function show(int $id): ?Subject
    {
        return Subject::find($id);
    }
}