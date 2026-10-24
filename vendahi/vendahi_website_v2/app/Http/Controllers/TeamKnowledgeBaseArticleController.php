<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * The tenant's OWN knowledge base: articles staff write for each other, stored
 * tenant-side in knowledge_base_articles.
 *
 * Deliberately separate from KnowledgeBaseArticleController, which serves the
 * super admin's central help centre (kb_articles) read-only. The two id spaces
 * are unrelated, which is why the frontend keeps them in separate tabs and
 * never mixes ids across them. knowledge_base_article_feedbacks is keyed by
 * CENTRAL article id (see the 2026_08_06 migration) and is not used here.
 */
class TeamKnowledgeBaseArticleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('api');
        $this->authorizeForUser($user, 'viewAny', KnowledgeBaseArticle::class);

        $query = KnowledgeBaseArticle::query()->with('group:id,name,slug');

        // Drafts and internal-only articles are for the people who maintain the
        // knowledge base; everyone else sees the published, non-internal set.
        if (! $this->canManage($user)) {
            $query->where('is_internal', false)->whereNotNull('published_at');
        }

        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }

        $groupId = $request->get('group_id');
        if ($groupId !== null && $groupId !== '') {
            $query->where('knowledge_base_article_group_id', $groupId);
        }

        $items = $query->orderBy('sort_order')->orderBy('title')->paginate(
            min((int) $request->get('per_page', 15), 100)
        );
        $items->getCollection()->transform(fn ($article) => $this->presentArticle($article));

        return response()->json($items);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user('api');
        $article = KnowledgeBaseArticle::with('group:id,name,slug')->findOrFail($id);

        $this->authorizeForUser($user, 'view', $article);
        // The policy only knows about is_internal; a draft is equally staff-only.
        if (! $article->published_at && ! $this->canManage($user)) {
            abort(404);
        }

        return response()->json($this->presentArticle($article));
    }

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', KnowledgeBaseArticle::class);

        $article = KnowledgeBaseArticle::create($this->validated($request));

        return response()->json($this->presentArticle($article->load('group:id,name,slug')), 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', KnowledgeBaseArticle::class);

        $article = KnowledgeBaseArticle::findOrFail($id);
        $article->update($this->validated($request, $article));

        return response()->json($this->presentArticle($article->load('group:id,name,slug')));
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', KnowledgeBaseArticle::class);

        KnowledgeBaseArticle::findOrFail($id)->delete();

        return response()->json(['ok' => true]);
    }

    /** Whoever may create articles also sees drafts and internal ones. */
    protected function canManage($user): bool
    {
        return Gate::forUser($user)->allows('create', KnowledgeBaseArticle::class);
    }

    protected function validated(Request $request, ?KnowledgeBaseArticle $existing = null): array
    {
        $data = $request->validate([
            'knowledge_base_article_group_id' => [
                'required', 'integer', Rule::exists('knowledge_base_article_groups', 'id'),
            ],
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'content' => ['nullable', 'string'],
            'is_internal' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $groupId = (int) $data['knowledge_base_article_group_id'];

        // An omitted optional field keeps whatever the article already has, so a
        // partial update (a publish toggle, say) cannot silently blank the body
        // or drop the internal flag. On create there is nothing to keep.
        return [
            'knowledge_base_article_group_id' => $groupId,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['slug'] ?? null, $data['title'], $groupId, $existing?->id),
            'content' => $request->has('content') ? (string) ($data['content'] ?? '') : (string) ($existing?->content ?? ''),
            'is_internal' => $request->has('is_internal')
                ? $request->boolean('is_internal')
                : (bool) ($existing?->is_internal ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? $existing?->sort_order ?? 0),
            'published_at' => $this->resolvePublishedAt($request, $data, $existing),
        ];
    }

    /**
     * published_at doubles as the draft flag (null = draft). An explicit date
     * wins — that is how an article gets backdated — otherwise the
     * Published/Draft switch toggles it, keeping the original date when an
     * already-published article is edited. Sending neither field leaves the
     * state alone.
     */
    protected function resolvePublishedAt(Request $request, array $data, ?KnowledgeBaseArticle $existing)
    {
        if ($request->filled('published_at')) {
            return $data['published_at'];
        }

        if ($request->has('published')) {
            if (! $request->boolean('published')) {
                return null;
            }

            return $existing?->published_at ?? now()->toDateString();
        }

        return $existing?->published_at;
    }

    /**
     * The table is unique on (group, slug), so dedupe within the target group
     * only — and re-check on update, since moving an article to another group
     * can collide with a slug that was free in the old one.
     */
    protected function uniqueSlug(?string $slug, string $title, int $groupId, ?int $ignoreId): string
    {
        $base = str($slug ?: $title)->slug('-')->toString() ?: 'article';
        $candidate = $base;
        $n = 1;

        while (KnowledgeBaseArticle::where('knowledge_base_article_group_id', $groupId)
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.(++$n);
        }

        return $candidate;
    }

    /** Same envelope the central help-centre endpoints use, plus the tenant-only flags. */
    protected function presentArticle(KnowledgeBaseArticle $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'is_internal' => (bool) $article->is_internal,
            'sort_order' => $article->sort_order,
            'published_at' => optional($article->published_at)->toDateString(),
            'knowledge_base_article_group_id' => $article->knowledge_base_article_group_id,
            'group' => $article->group ? [
                'id' => $article->group->id,
                'name' => $article->group->name,
                'slug' => $article->group->slug,
            ] : null,
            'updated_at' => optional($article->updated_at)->toIso8601String(),
        ];
    }
}
