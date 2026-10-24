<?php

namespace Modules\Hostel\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Hostel\Models\HostelCategory;

class HostelCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = HostelCategory::select(
            'hostel_categories.id',
            'hostel_categories.standard',
            'hostel_categories.note',
            'hostel_categories.hostel_id',
            'hostel_categories.hostel_fee',
            'hostels.hostel_name'
        )
            ->join('hostels', 'hostels.id', '=', 'hostel_categories.hostel_id')
            ->orderByDesc('hostel_categories.id');

        // ✅ Hostel ID wise filter
        if ($request->filled('hostel_id')) {
            $query->where('hostel_categories.hostel_id', $request->hostel_id);
        }

        // 🔍 Optional fast search
        if ($search = trim($request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('hostel_categories.standard', 'like', "{$search}%")
                    ->orWhere('hostels.hostel_name', 'like', "{$search}%")
                    ->orWhere('hostel_categories.note', 'like', "{$search}%");
            });
        }

        // 📄 Pagination (default 20)
        $perPage = (int) $request->get('per_page', 20);
        $categories = $query->paginate($perPage);

        return $this->responseSuccess($categories, 'Hostel categories fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'standard' => 'required',
            'hostel_fee' => 'required|numeric',
            'note' => 'nullable',
        ]);

        $category = new HostelCategory;
        $category->institute_id = get_institute_id();
        $category->branch_id = get_branch_id();
        $category->hostel_id = $request->hostel_id;
        $category->standard = $request->standard;
        $category->hostel_fee = $request->hostel_fee;
        $category->note = $request->note;
        $category->save();

        return $this->responseSuccess($category, 'Hostel Category create successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $category = HostelCategory::find($id);
        if (! $category) {
            return $this->responseError([], _lang('Something went wrong. Hostel Category can not be found.'));
        }

        return $this->responseSuccess($category, 'Hostel Category create successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'standard' => 'required',
            'hostel_fee' => 'required|numeric',
            'note' => 'nullable',
        ]);

        $category = HostelCategory::find($id);
        if (! $category) {
            return $this->responseError([], _lang('Something went wrong. Hostel Category can not be found.'));
        }

        $category->hostel_id = $request->hostel_id;
        $category->standard = $request->standard;
        $category->hostel_fee = $request->hostel_fee;
        $category->note = $request->note;
        $category->save();

        return $this->responseSuccess($category, 'Hostel Category update successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $category = HostelCategory::find($id);
        if (! $category) {
            return $this->responseError([], _lang('Something went wrong. Hostel Category can not be found.'));
        }

        $category->delete();

        return $this->responseSuccess([], 'Hostel Category delete successfully.');
    }
}
