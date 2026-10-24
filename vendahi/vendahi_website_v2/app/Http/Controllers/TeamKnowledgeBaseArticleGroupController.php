<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseArticleGroup;
use Illuminate\Http\Request;

/**
 * Groups for the tenant's OWN knowledge base (tenant-local table
 * knowledge_base_article_groups).
 *
 * The sibling KnowledgeBaseArticleGroupController serves the super admin's
 * central categories and stays read-only; these groups belong to the tenant.
 */
class TeamKnowledgeBaseArticleGroupController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'viewAny', KnowledgeBaseArticleGroup::class);

        $groups = KnowledgeBaseArticleGroup::query()
            ->withCount('articles')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'sort_order']);

        return response()->json($groups);
    }

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', KnowledgeBaseArticleGroup::class);

        $group = KnowledgeBaseArticleGroup::create($this->validated($request));

        return response()->json($group, 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', KnowledgeBaseArticleGroup::class);

        $group = KnowledgeBaseArticleGroup::findOrFail($id);
        $group->update($this->validated($request, $group->id));

        return response()->json($group);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', KnowledgeBaseArticleGroup::class);

        $group = KnowledgeBaseArticleGroup::findOrFail($id);

        // knowledge_base_articles.knowledge_base_article_group_id is NOT NULL,
        // so a group can only go once it is empty; deleting it while articles
        // point at it would leave them unreachable.
        if (KnowledgeBaseArticle::where('knowledge_base_article_group_id', $group->id)->exists()) {
            return response()->json([
                'status' => 'group_not_empty',
                'message' => __('Move or delete the articles in this group first.'),
            ], 422);
        }

        $group->delete();

        return response()->json(['ok' => true]);
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name'], $ignoreId);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    /**
     * The table's slug column is globally unique, and these slugs are internal
     * (tenant groups have no public URL), so a collision gets a -2, -3, … suffix
     * rather than a validation error the user has to resolve by hand.
     */
    protected function uniqueSlug(?string $slug, string $name, ?int $ignoreId): string
    {
        $base = str($slug ?: $name)->slug('-')->toString() ?: 'group';
        $candidate = $base;
        $n = 1;

        while (KnowledgeBaseArticleGroup::where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.(++$n);
        }

        return $candidate;
    }
}
