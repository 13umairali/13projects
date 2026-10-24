<?php

namespace App\Http\Controllers;

use App\Models\Central\KbCategory;
use Illuminate\Http\Request;

/**
 * Tenant-facing knowledge base categories (exposed to the frontend as "groups").
 * Read-only: categories are managed by the super admin in the central database.
 */
class KnowledgeBaseArticleGroupController extends Controller
{
    /**
     * Categories for the filter dropdown - only those with at least one published article.
     */
    public function forFilter(Request $request)
    {
        $categories = KbCategory::query()
            ->whereHas('articles', function ($q) {
                $q->where('is_published', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json($categories);
    }
}
