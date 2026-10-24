<?php

namespace App\Http\Controllers\Central\Super;

use App\Http\Controllers\Controller;
use App\Models\Central\BlogCategory;
use App\Models\Central\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogController extends Controller
{
    // ── Index: stats, categories and posts ──

    public function index(): View
    {
        $categories = BlogCategory::withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $posts = BlogPost::with('category')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalPosts = BlogPost::count();
        $publishedPosts = BlogPost::published()->count();

        return view('central.super.blog.index', compact('categories', 'posts', 'totalPosts', 'publishedPosts'));
    }

    // ── Categories ──

    public function createCategory(): View
    {
        return view('central.super.blog.category-form', ['blogCategory' => null]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:190'],
            'slug'        => ['required', 'string', 'max:190', 'alpha_dash', 'unique:central.blog_categories,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;

        BlogCategory::create($data);

        return redirect()->route('super.blog.index')->with('success', "Category \"{$data['name']}\" created.");
    }

    public function editCategory(BlogCategory $blogCategory): View
    {
        return view('central.super.blog.category-form', compact('blogCategory'));
    }

    public function updateCategory(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:190'],
            'slug'        => ['required', 'string', 'max:190', 'alpha_dash', Rule::unique('central.blog_categories', 'slug')->ignore($blogCategory->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $blogCategory->update($data);

        return redirect()->route('super.blog.index')->with('success', "Category \"{$blogCategory->name}\" updated.");
    }

    public function destroyCategory(BlogCategory $blogCategory): RedirectResponse
    {
        $name = $blogCategory->name;
        $blogCategory->delete(); // posts keep existing, category_id set null

        return redirect()->route('super.blog.index')->with('success', "Category \"{$name}\" deleted.");
    }

    // ── Posts ──

    public function createPost(Request $request): View
    {
        $categories = BlogCategory::ordered()->get();
        $preselectedCategory = $request->query('category');

        return view('central.super.blog.post-form', [
            'post'       => null,
            'categories' => $categories,
            'preselectedCategory' => $preselectedCategory,
        ]);
    }

    public function storePost(Request $request): RedirectResponse
    {
        $data = $this->validatePost($request);

        $data['is_published'] = $request->has('is_published');
        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        $data['central_user_id'] = Auth::guard('central')->id();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeImage($request, 'cover_image');
        }

        $post = BlogPost::create($data);

        return redirect()->route('super.blog.index')->with('success', "Post \"{$post->title}\" created.");
    }

    public function editPost(BlogPost $post): View
    {
        $categories = BlogCategory::ordered()->get();

        return view('central.super.blog.post-form', compact('post', 'categories'));
    }

    public function updatePost(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->validatePost($request, $post);

        $data['is_published'] = $request->has('is_published');
        if ($data['is_published'] && empty($data['published_at']) && ! $post->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteImage($post->cover_image);
            $data['cover_image'] = $this->storeImage($request, 'cover_image');
        } elseif ($request->boolean('remove_cover_image')) {
            $this->deleteImage($post->cover_image);
            $data['cover_image'] = null;
        }

        $post->update($data);

        return redirect()->route('super.blog.index')->with('success', "Post \"{$post->title}\" updated.");
    }

    public function destroyPost(BlogPost $post): RedirectResponse
    {
        $title = $post->title;
        $this->deleteImage($post->cover_image);
        $post->delete();

        return redirect()->route('super.blog.index')->with('success', "Post \"{$title}\" deleted.");
    }

    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        return $request->validate([
            'blog_category_id' => ['nullable', 'integer', 'exists:central.blog_categories,id'],
            'title'            => ['required', 'string', 'max:190'],
            'slug'             => ['required', 'string', 'max:190', 'alpha_dash', $post ? Rule::unique('central.blog_posts', 'slug')->ignore($post->id) : Rule::unique('central.blog_posts', 'slug')],
            'excerpt'          => ['nullable', 'string', 'max:500'],
            'content'          => ['nullable', 'string'],
            'cover_image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'meta_title'       => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_published'     => ['nullable'],
            'published_at'     => ['nullable', 'date'],
        ]);
    }

    private function storeImage(Request $request, string $field, string $folder = 'super/blog'): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }
        $file = $request->file($field);
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $uploadDir = public_path('images/' . $folder);
        $file->move($uploadDir, $filename);

        return 'images/' . $folder . '/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }
}
