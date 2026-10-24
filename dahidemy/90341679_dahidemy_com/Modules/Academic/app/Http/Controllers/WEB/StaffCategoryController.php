<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Academic\Http\Requests\StaffCategoryCreateRequest;
use Modules\Academic\Http\Requests\StaffCategoryUpdateRequest;
use Modules\Academic\Models\StaffCategory;
use Modules\Academic\Services\StaffCategoryService;
use RealRashid\SweetAlert\Facades\Alert;

/**
 * Lets each institute manage its own staff categories (Administration /
 * Teaching Staff / Supporting Staff, or whatever structure they prefer —
 * per the request, this is intentionally institute-editable rather than a
 * fixed global list).
 */
class StaffCategoryController extends Controller
{
    public function __construct(
        private readonly StaffCategoryService $staffCategoryService,
    ) {}

    public function index(): Factory|View
    {
        $staffCategories = $this->staffCategoryService->getStaffCategories();

        return view('academic::staff_categories.index', compact('staffCategories'));
    }

    public function store(StaffCategoryCreateRequest $request): Redirector|RedirectResponse
    {
        $this->staffCategoryService->createStaffCategory($request->validated());

        Alert::toast(_lang('Staff category created successfully.'), 'success');

        return redirect()->route('staff-categories.index');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $staffCategory = $this->staffCategoryService->findStaffCategoryById($id);

        if (! $staffCategory) {
            Alert::error('Error', _lang('Staff category not found.'));

            return redirect()->route('staff-categories.index');
        }

        return view('academic::staff_categories.edit', compact('staffCategory'));
    }

    public function update(StaffCategoryUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        $this->staffCategoryService->updateStaffCategory($request->validated(), $id);

        Alert::toast(_lang('Staff category updated successfully.'), 'success');

        return redirect()->route('staff-categories.index');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $category = StaffCategory::withCount('staffs')->find($id);

        if (! $category) {
            Alert::error('Error', _lang('Staff category not found.'));

            return redirect()->route('staff-categories.index');
        }

        // Deleting a category shouldn't silently orphan or delete the staff
        // in it -- require the institute to reassign them first, since a
        // staff member losing their category unexpectedly (or being
        // cascade-deleted) would be a much worse surprise than an error
        // message telling them what to do.
        if ($category->staffs_count > 0) {
            Alert::error('Error', _lang('This category still has staff assigned to it. Move them to another category first.'));

            return redirect()->route('staff-categories.index');
        }

        $this->staffCategoryService->deleteStaffCategoryById($id);

        Alert::toast(_lang('Staff category deleted successfully.'), 'success');

        return redirect()->route('staff-categories.index');
    }
}
