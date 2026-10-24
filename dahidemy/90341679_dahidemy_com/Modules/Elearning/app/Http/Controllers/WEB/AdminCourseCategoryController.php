<?php

namespace Modules\Elearning\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Elearning\Models\CourseCategory;
use RealRashid\SweetAlert\Facades\Alert;

class AdminCourseCategoryController extends Controller
{
    public function index()
    {
        $categories = CourseCategory::where('institute_id', get_institute_id())
            ->orderBy('priority')
            ->get();

        return view('elearning::admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = CourseCategory::where('institute_id', get_institute_id())->whereNull('parent_id')->get();

        return view('elearning::admin.categories.form', ['category' => new CourseCategory, 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']).'-'.\Illuminate\Support\Str::random(4);

        CourseCategory::create([
            ...$validated,
            'institute_id' => get_institute_id(),
        ]);

        Alert::success('Success', 'Category created.');

        return redirect()->route('elearning.admin.categories.index');
    }

    public function edit(int $id)
    {
        $category = CourseCategory::where('institute_id', get_institute_id())->findOrFail($id);
        $parents = CourseCategory::where('institute_id', get_institute_id())->whereNull('parent_id')->where('id', '!=', $id)->get();

        return view('elearning::admin.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, int $id)
    {
        $category = CourseCategory::where('institute_id', get_institute_id())->findOrFail($id);
        $category->update($this->validated($request));

        Alert::success('Success', 'Category updated.');

        return redirect()->route('elearning.admin.categories.index');
    }

    public function destroy(int $id)
    {
        $category = CourseCategory::where('institute_id', get_institute_id())->findOrFail($id);

        if ($category->courses()->exists()) {
            Alert::error('Error', 'This category still has courses in it — move or delete those first.');

            return back();
        }

        $category->delete();

        Alert::success('Success', 'Category deleted.');

        return redirect()->route('elearning.admin.categories.index');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'parent_id' => ['nullable', 'exists:course_categories,id'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'integer'],
            'enable_homepage' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
        ]);

        $data['enable_homepage'] = $request->boolean('enable_homepage');
        $data['status'] = $request->boolean('status', true);
        $data['priority'] = $data['priority'] ?? 0;

        return $data;
    }
}
