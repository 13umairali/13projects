<?php

namespace Modules\Blog\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Models\BlogCategory;
use RealRashid\SweetAlert\Facades\Alert;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::where('institute_id', get_institute_id())
            ->withCount('posts')
            ->orderBy('name')
            ->get();

        return view('blog::admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('blog::admin.categories.form', ['category' => new BlogCategory]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        BlogCategory::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
            'created_by' => auth()->id(),
        ]);

        Alert::success('Success', 'Category created.');

        return redirect()->route('blog.admin.categories.index');
    }

    public function edit(int $id)
    {
        $category = BlogCategory::where('institute_id', get_institute_id())->findOrFail($id);

        return view('blog::admin.categories.form', compact('category'));
    }

    public function update(Request $request, int $id)
    {
        $category = BlogCategory::where('institute_id', get_institute_id())->findOrFail($id);
        $category->update($this->validated($request));

        Alert::success('Success', 'Category updated.');

        return redirect()->route('blog.admin.categories.index');
    }

    public function destroy(int $id)
    {
        $category = BlogCategory::where('institute_id', get_institute_id())->findOrFail($id);

        if ($category->posts()->exists()) {
            Alert::error('Error', 'This category still has posts in it — move or delete those first.');

            return back();
        }

        $category->delete();

        Alert::success('Success', 'Category deleted.');

        return redirect()->route('blog.admin.categories.index');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->boolean('status', true) ? BlogCategory::STATUS_ACTIVE : BlogCategory::STATUS_INACTIVE;

        return $data;
    }
}
