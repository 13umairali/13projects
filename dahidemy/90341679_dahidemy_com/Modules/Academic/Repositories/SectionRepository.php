<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use App\Services\AcademicContextService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Section;

class SectionRepository implements BaseRepositoryInterface
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function all(): Collection
    {
        return Section::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Section::query();

        // A section whose parent class was soft-deleted is excluded here
        // too - this repository is the actual data source behind
        // dropdowns/lists elsewhere in the app, and previously returned
        // such sections since it never checked the parent class's
        // deletion status at all.
        return $query->select('sections.*')
            ->join('classes', 'classes.id', '=', 'sections.class_id')
            ->whereNull('classes.deleted_at')
            ->where('sections.institute_id', get_institute_id())
            ->where('sections.branch_id', get_branch_id())
            ->paginate($perPage);
    }

    public function create(array $data): ?Section
    {
        return Section::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Section::findOrFail($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Section::destroy($id);
    }

    public function show(int $id): ?Section
    {
        return Section::find($id);
    }

    public function getSections(): Collection
    {
        return Section::select('id', 'section_name', 'class_id')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('rank')
            ->get();
    }

    public function getSectionsByClassId(int $id): Collection
    {
        return $this->academicContext->sectionsForClass($id);
    }
}