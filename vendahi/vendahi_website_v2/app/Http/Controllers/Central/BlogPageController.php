<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\BlogCategory;
use App\Models\Central\BlogPost;
use App\Services\LandingCmsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogPageController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');

        $posts = BlogPost::published()
            ->with('category')
            ->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
            })
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = BlogCategory::ordered()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->get()
            ->filter(fn ($cat) => $cat->posts_count > 0)
            ->values();

        $activeCategory = $categorySlug
            ? $categories->firstWhere('slug', $categorySlug)
            : null;

        $cms = app(LandingCmsService::class);
        $footer = $cms->getSection('footer');
        $seo = $cms->getSection('seo');

        return view('central.blog.index', compact('posts', 'categories', 'activeCategory', 'footer', 'seo'));
    }

    public function show(string $slug): View
    {
        $post = BlogPost::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPosts = BlogPost::published()
            ->with('category')
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $cms = app(LandingCmsService::class);
        $footer = $cms->getSection('footer');
        $seo = $cms->getSection('seo');

        return view('central.blog.show', compact('post', 'relatedPosts', 'footer', 'seo'));
    }
}
