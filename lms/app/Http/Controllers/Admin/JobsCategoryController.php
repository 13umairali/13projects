<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use App\Models\Translation\JobCategoryTranslation;
use Illuminate\Http\Request;

class JobsCategoryController extends Controller
{
    public function index()
    {
        removeContentLocale();
        $this->authorize('admin_jobs_categories');

        $categories = JobCategory::where('parent_id', null)
            ->orderBy('id', 'desc')
            ->withCount([
                'jobs',
                'subCategories'
            ])
            ->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.categories'),
            'categories' => $categories
        ];

        return view('admin.jobs.categories.lists', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('admin_jobs_categories');

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('admin/main.category_new_page_title'),
            'locale' => mb_strtolower($locale),
        ];

        return view('admin.jobs.categories.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_jobs_categories');

        $this->validate($request, [
            'title' => 'required|min:3|max:255',
            'subtitle' => 'required|min:3',
            'icon' => 'required',
        ]);

        $order = JobCategory::query()->whereNull('parent_id')->count() + 1;

        $data = $request->all();
        $category = JobCategory::query()->create([
            'icon' => $data['icon'] ?? null,
            'order' => $order,
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
        ]);

        // Extra Data
        $this->handleExtraData($request, $category);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.category_created_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/categories/{$category->id}/edit"))->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_jobs_categories');

        $category = JobCategory::query()->findOrFail($id);
        $subCategories = $category->subCategories()
            ->orderBy('order', 'asc')
            ->get();

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('categories.edit_category'),
            'category' => $category,
            'subCategories' => $subCategories,
            'locale' => mb_strtolower($locale),
        ];

        return view('admin.jobs.categories.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_jobs_categories');

        $this->validate($request, [
            'title' => 'required|min:3|max:255',
            'subtitle' => 'required|min:3',
            'icon' => 'required',
        ]);

        $data = $request->all();

        $category = JobCategory::query()->findOrFail($id);
        $category->update([
            'icon' => $data['icon'],
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
        ]);

        // Extra Data
        $this->handleExtraData($request, $category);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.category_updated_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/categories/{$category->id}/edit"))->with(['toast' => $toastData]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_jobs_categories');

        $category = JobCategory::query()->findOrFail($id);

        if (!empty($category)) {
            JobCategory::where('parent_id', $category->id)->delete();

            $category->delete();
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.category_deleted_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/categories"))->with(['toast' => $toastData]);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $option = $request->get('option', null);

        $query = JobCategory::query()->select('id')
            ->whereTranslationLike('title', "%$term%");

        /*if (!empty($option)) {

        }*/

        $categories = $query->get();

        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'title' => $category->title,
            ];
        }

        return response()->json($result, 200);
    }

    private function handleExtraData(Request $request, $category)
    {
        $data = $request->all();

        JobCategoryTranslation::updateOrCreate([
            'job_category_id' => $category->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'bottom_seo_title' => $data['bottom_seo_title'] ?? null,
            'bottom_seo_description' => $data['bottom_seo_description'] ?? null,
        ]);

        $hasSubCategories = (!empty($request->get('has_sub')) and $request->get('has_sub') == 'on');
        $this->setSubCategory($category, $request->get('sub_categories'), $hasSubCategories, $data['locale']);
    }

    private function setSubCategory(JobCategory $category, $subCategories, $hasSubCategories, $locale)
    {
        $order = 1;
        $oldIds = [];

        if ($hasSubCategories and !empty($subCategories) and count($subCategories)) {
            foreach ($subCategories as $key => $subCategory) {
                $jobCategoryItem = JobCategory::where('id', $key)->first();

                if (is_numeric($key)) {
                    $oldIds[] = $key;
                }

                if (!empty($subCategory['title'])) {
                    if (!empty($jobCategoryItem)) {
                        $jobCategoryItem->update([
                            'order' => $order,
                            'icon' => $subCategory['icon'] ?? null,
                            'enable' => (!empty($subCategory['enable']) and $subCategory['enable'] == 'on'),
                        ]);
                    } else {
                        $jobCategoryItem = JobCategory::create([
                            'parent_id' => $category->id,
                            'order' => $order,
                            'icon' => $subCategory['icon'] ?? null,
                            'enable' => (!empty($subCategory['enable']) and $subCategory['enable'] == 'on'),
                        ]);

                        $oldIds[] = $jobCategoryItem->id;
                    }

                    JobCategoryTranslation::updateOrCreate([
                        'job_category_id' => $jobCategoryItem->id,
                        'locale' => mb_strtolower($locale),
                    ], [
                        'title' => $subCategory['title'],
                        'subtitle' => $subCategory['subtitle'],
                        'bottom_seo_title' => $subCategory['bottom_seo_title'] ?? null,
                        'bottom_seo_description' => $subCategory['bottom_seo_description'] ?? null,
                    ]);

                    $order += 1;
                }
            }
        }

        JobCategory::where('parent_id', $category->id)
            ->whereNotIn('id', $oldIds)
            ->delete();

        return true;
    }
}
