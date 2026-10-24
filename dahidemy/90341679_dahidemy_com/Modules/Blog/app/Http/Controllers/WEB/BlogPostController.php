<?php

namespace Modules\Blog\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogPost;
use RealRashid\SweetAlert\Facades\Alert;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::where('institute_id', get_institute_id())
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('blog::admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('blog::admin.posts.form', [
            'post' => new BlogPost,
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = fileUploader('blog_posts/', $request->file('featured_image')->getClientOriginalExtension(), $request->file('featured_image'));
        }

        $isPublished = $validated['status'] === BlogPost::STATUS_PUBLISHED;

        BlogPost::create([
            ...$validated,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'slug' => Str::slug($validated['title']).'-'.Str::random(5),
            'published_at' => $isPublished ? now() : null,
            'created_by' => auth()->id(),
        ]);

        Alert::success('Success', 'Post created.');

        return redirect()->route('blog.admin.posts.index');
    }

    public function edit(int $id)
    {
        $post = BlogPost::where('institute_id', get_institute_id())->findOrFail($id);

        return view('blog::admin.posts.form', [
            'post' => $post,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $post = BlogPost::where('institute_id', get_institute_id())->findOrFail($id);
        $validated = $this->validated($request);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = fileUploader('blog_posts/', $request->file('featured_image')->getClientOriginalExtension(), $request->file('featured_image'), $post->featured_image);
        }

        $wasPublishedBefore = $post->isPublished();
        $isPublishedNow = $validated['status'] === BlogPost::STATUS_PUBLISHED;

        // Only stamp published_at the first time it goes live, so re-editing
        // an already-published post doesn't keep bumping its publish date.
        if (! $wasPublishedBefore && $isPublishedNow) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        Alert::success('Success', 'Post updated.');

        return redirect()->route('blog.admin.posts.index');
    }

    public function destroy(int $id)
    {
        $post = BlogPost::where('institute_id', get_institute_id())->findOrFail($id);

        if ($post->featured_image) {
            fileRemover('blog_posts/', $post->featured_image);
        }
        $post->delete();

        Alert::success('Success', 'Post deleted.');

        return redirect()->route('blog.admin.posts.index');
    }

    public function toggleStatus(int $id)
    {
        $post = BlogPost::where('institute_id', get_institute_id())->findOrFail($id);
        $goingLive = ! $post->isPublished();

        $post->status = $goingLive ? BlogPost::STATUS_PUBLISHED : BlogPost::STATUS_DRAFT;
        if ($goingLive && ! $post->published_at) {
            $post->published_at = now();
        }
        $post->save();

        return back();
    }

    private function categories()
    {
        return BlogCategory::where('institute_id', get_institute_id())->orderBy('name')->get();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'category_id' => ['nullable', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'author_name' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
        ]);

        unset($data['featured_image']); // handled separately
        $data['status'] = $request->boolean('status') ? BlogPost::STATUS_PUBLISHED : BlogPost::STATUS_DRAFT;

        return $data;
    }
}
