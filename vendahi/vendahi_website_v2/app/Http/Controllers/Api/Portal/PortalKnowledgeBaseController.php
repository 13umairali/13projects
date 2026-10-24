<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Models\Central\KbArticle;
use App\Models\Central\KbCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Portal knowledge base. Articles live in the central database (kb_articles /
 * kb_categories, managed by the super admin); categories are exposed to the
 * portal frontend under the legacy "group" field names.
 */
class PortalKnowledgeBaseController extends Controller
{
    /**
     * GET /api/portal/knowledge-base - list published articles, grouped.
     */
    public function index(Request $request)
    {
        $portalClient = Auth::guard('portal')->user();
        $this->assertPortalActive($portalClient);

        $search = trim((string) $request->input('q', ''));
        $groupId = $request->input('group_id');

        $articles = KbArticle::query()
            ->where('is_published', true)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%");
                });
            })
            ->when($groupId, fn ($q) => $q->where('kb_category_id', $groupId))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'kb_category_id', 'title', 'slug', 'sort_order'])
            ->map(fn ($a) => [
                'id' => $a->id,
                'knowledge_base_article_group_id' => $a->kb_category_id,
                'title' => $a->title,
                'slug' => $a->slug,
                'sort_order' => $a->sort_order,
            ]);

        $groups = KbCategory::query()
            ->whereHas('articles', fn ($q) => $q->where('is_published', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'groups' => $groups,
            'articles' => $articles,
        ]);
    }

    /**
     * GET /api/portal/knowledge-base/{slug} - published article by slug.
     */
    public function show(Request $request, $slug)
    {
        $portalClient = Auth::guard('portal')->user();
        $this->assertPortalActive($portalClient);

        $article = KbArticle::query()
            ->where('is_published', true)
            ->where('slug', $slug)
            ->with('category:id,name,slug')
            ->firstOrFail();

        return response()->json([
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'group' => $article->category ? [
                'id' => $article->category->id,
                'name' => $article->category->name,
                'slug' => $article->category->slug,
            ] : null,
            'updated_at' => optional($article->updated_at)->toIso8601String(),
        ]);
    }

    private function assertPortalActive($portalClient): void
    {
        if ((int) $portalClient->status !== 1) {
            abort(403, 'Portal access is disabled');
        }
    }
}
