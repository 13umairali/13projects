<?php

namespace Modules\Blog\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogPost;

class BlogPageController extends Controller
{
    public function index(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $categorySlug = $request->query('category');
        $category = $categorySlug
            ? BlogCategory::where('institute_id', $institute->id)->where('slug', $categorySlug)->first()
            : null;

        $posts = BlogPost::where('institute_id', $institute->id)
            ->published()
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->with('category')
            ->orderByDesc('published_at')
            ->paginate(9);

        $categories = BlogCategory::where('institute_id', $institute->id)
            ->where('status', BlogCategory::STATUS_ACTIVE)
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        return view('blog::public.index', compact('institute', 'posts', 'categories', 'category'));
    }

    public function show(Request $request, string $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        abort_if(! $institute, 404);

        $post = BlogPost::where('institute_id', $institute->id)
            ->where('slug', $slug)
            ->published()
            ->with('category')
            ->firstOrFail();

        $post->increment('views');

        $relatedPosts = BlogPost::where('institute_id', $institute->id)
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog::public.show', compact('institute', 'post', 'relatedPosts'));
    }
}
