<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use App\Models\JobCategoryFilter;
use App\Models\JobCategoryFilterOption;
use App\Models\Translation\JobCategoryFilterOptionTranslation;
use App\Models\Translation\JobCategoryFilterTranslation;
use Illuminate\Http\Request;

class JobsFilterController extends Controller
{
    public function index()
    {
        $this->authorize('admin_jobs_categories');

        $filters = JobCategoryFilter::query()
            ->with([
                'category'
            ])
            ->withCount([
                'options'
            ])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => trans('admin/main.filters_list_page_title'),
            'filters' => $filters,
        ];

        return view('admin.jobs.filters.lists', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('admin_jobs_categories');

        $categories = JobCategory::query()->where('parent_id', null)
            ->with('subCategories')
            ->get();

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('admin/main.filter_new_page_title'),
            'categories' => $categories,
            'locale' => mb_strtolower($locale)
        ];

        return view('admin.jobs.filters.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_jobs_categories');

        $this->validate($request, [
            'title' => 'required|min:3|max:255',
            'category_id' => 'required|exists:job_categories,id',
        ]);

        $data = $request->all();

        $filter = JobCategoryFilter::query()->create([
            'category_id' => $data['category_id'],
        ]);

        // Extra Data
        $this->handleExtraData($request, $filter);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.category_filter_created_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/filters/{$filter->id}/edit"))->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_jobs_categories');

        $filter = JobCategoryFilter::query()->findOrFail($id);
        $categories = JobCategory::query()->where('parent_id', null)
            ->with('subCategories')
            ->get();

        $filterOptions = JobCategoryFilterOption::query()->where('filter_id', $filter->id)
            ->orderBy('order', 'asc')
            ->get();

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('admin/main.admin_filters_edit'),
            'filter' => $filter,
            'categories' => $categories,
            'filterOptions' => $filterOptions,
            'locale' => mb_strtolower($locale),
        ];

        return view('admin.jobs.filters.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_jobs_categories');

        $this->validate($request, [
            'title' => 'required|min:3|max:255',
            'category_id' => 'required|exists:job_categories,id',
        ]);

        $data = $request->all();

        $filter = JobCategoryFilter::query()->findOrFail($id);
        $filter->update([
            'category_id' => $data['category_id'],
        ]);

        // Extra Data
        $this->handleExtraData($request, $filter);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.category_filter_updated_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/filters/{$filter->id}/edit"))->with(['toast' => $toastData]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_jobs_categories');

        $filter = JobCategoryFilter::query()->findOrFail($id);

        $filter->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.category_filter_deleted_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/filters"))->with(['toast' => $toastData]);
    }

    private function handleExtraData(Request $request, $filter)
    {
        $data = $request->all();

        JobCategoryFilterTranslation::query()->updateOrCreate([
            'job_category_filter_id' => $filter->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
        ]);


        $filterOptions = !empty($data['sub_filters']) ? $data['sub_filters'] : [];
        $this->setSubFilters($filter, $filterOptions, $data['locale']);
    }

    private function setSubFilters(JobCategoryFilter $filter, $filterOptions, $locale)
    {

        $allFilterOptionsIds = $filter->options->pluck('id')->toArray();

        if (!empty($filterOptions) and count($filterOptions)) {
            $order = 1;

            foreach ($filterOptions as $key => $filterOption) {
                if (!empty($filterOption['title'])) {
                    $filterOptionItem = JobCategoryFilterOption::query()->where('filter_id', $filter->id)
                        ->where('id', $key)
                        ->first();

                    if (!empty($filterOptionItem)) {

                        $oldIdsSearch = array_search($key, $allFilterOptionsIds);

                        if ($oldIdsSearch !== -1) {
                            unset($allFilterOptionsIds[$oldIdsSearch]);
                        }

                        $filterOptionItem->update([
                            'order' => $order,
                        ]);
                    } else {
                        $filterOptionItem = JobCategoryFilterOption::query()->create([
                            'title' => $filterOption['title'],
                            'filter_id' => $filter->id,
                            'order' => $order,
                        ]);
                    }

                    JobCategoryFilterOptionTranslation::query()->updateOrCreate([
                        'filter_option_id' => $filterOptionItem->id,
                        'locale' => mb_strtolower($locale),
                    ], [
                        'title' => $filterOption['title'],
                    ]);

                    $order += 1;
                }
            }
        }

        if (!empty($allFilterOptionsIds) and count($allFilterOptionsIds)) {
            JobCategoryFilterOption::whereIn('id', $allFilterOptionsIds)->delete();
        }
    }


}
