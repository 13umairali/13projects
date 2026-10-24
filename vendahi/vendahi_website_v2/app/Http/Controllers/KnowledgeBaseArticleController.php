<?php

namespace App\Http\Controllers;

use App\Models\Central\KbArticle;
use App\Models\KnowledgeBaseArticleFeedback;
use Illuminate\Http\Request;

/**
 * Tenant-facing knowledge base. Articles are authored by the super admin in the
 * central database (kb_articles / kb_categories); tenants read the published
 * ones directly. Only feedback is stored tenant-side, keyed by the central
 * article id.
 */
class KnowledgeBaseArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = KbArticle::query()
            ->where('is_published', true)
            ->with('category:id,name,slug');

        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }
        $groupId = $request->get('group_id');
        if ($groupId !== null && $groupId !== '') {
            $query->where('kb_category_id', $groupId);
        }

        $items = $query->orderBy('sort_order')->orderBy('title')->paginate(
            min((int) $request->get('per_page', 15), 50)
        );
        $items->getCollection()->transform(fn ($article) => $this->presentArticle($article));

        return response()->json($items);
    }

    public function show(Request $request, int $id)
    {
        $article = KbArticle::query()
            ->where('is_published', true)
            ->with('category:id,name,slug')
            ->findOrFail($id);

        $data = $this->presentArticle($article);
        $data['feedback_helpful_count'] = KnowledgeBaseArticleFeedback::where('knowledge_base_article_id', $article->id)
            ->where('helpful', true)->count();
        $data['feedback_not_helpful_count'] = KnowledgeBaseArticleFeedback::where('knowledge_base_article_id', $article->id)
            ->where('helpful', false)->count();

        return response()->json($data);
    }

    public function submitFeedback(Request $request, int $id)
    {
        $article = KbArticle::query()->where('is_published', true)->findOrFail($id);

        $data = $request->validate([
            'helpful' => ['required', 'boolean'],
        ]);

        $user = $request->user('api');
        $existing = KnowledgeBaseArticleFeedback::where('knowledge_base_article_id', $article->id)
            ->where('user_id', $user->id)
            ->first();
        if ($existing) {
            $existing->update(['helpful' => (bool) $data['helpful']]);

            return response()->json(['ok' => true, 'updated' => true]);
        }

        KnowledgeBaseArticleFeedback::create([
            'knowledge_base_article_id' => $article->id,
            'user_id' => $user->id,
            'helpful' => (bool) $data['helpful'],
        ]);

        return response()->json(['ok' => true]);
    }

    protected function presentArticle(KbArticle $article): array
    {
        // Keep the shape the tenant frontend expects: category exposed as "group".
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'sort_order' => $article->sort_order,
            'group' => $article->category ? [
                'id' => $article->category->id,
                'name' => $article->category->name,
                'slug' => $article->category->slug,
            ] : null,
            'updated_at' => optional($article->updated_at)->toIso8601String(),
        ];
    }
}
