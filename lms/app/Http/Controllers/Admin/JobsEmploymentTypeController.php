<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobEmploymentType;
use App\Models\Translation\JobEmploymentTypeTranslation;
use Illuminate\Http\Request;

class JobsEmploymentTypeController extends Controller
{
    public function index()
    {
        $this->authorize('admin_jobs_employment_types');

        $employmentTypes = JobEmploymentType::query()
            ->withCount([
                'jobs'
            ])
            ->paginate(10);

        $data = [
            'pageTitle' => trans('update.employment_types'),
            'employmentTypes' => $employmentTypes,
        ];

        return view('admin.jobs.employment_types.lists', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('admin_jobs_employment_types');

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('update.new_employment_type'),
            'locale' => mb_strtolower($locale)
        ];

        return view('admin.jobs.employment_types.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_jobs_employment_types');

        $this->validate($request, [
            'title' => 'required|max:255',
            'icon' => 'required',
        ]);

        $data = $request->all();

        $employmentType = JobEmploymentType::query()->create([
            'icon' => $data['icon'],
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
        ]);

        // Extra Data
        $this->handleExtraData($request, $employmentType);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.employment_type_created_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/employment-types/{$employmentType->id}/edit"))->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_jobs_employment_types');

        $employmentType = JobEmploymentType::query()->findOrFail($id);
        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('update.edit_employment_type'),
            'employmentType' => $employmentType,
            'locale' => mb_strtolower($locale),
        ];

        return view('admin.jobs.employment_types.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_jobs_employment_types');

        $this->validate($request, [
            'title' => 'required|max:255',
            'icon' => 'required',
        ]);

        $data = $request->all();

        $employmentType = JobEmploymentType::query()->findOrFail($id);
        $employmentType->update([
            'icon' => $data['icon'],
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
        ]);

        // Extra Data
        $this->handleExtraData($request, $employmentType);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.employment_type_updated_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/employment-types/{$employmentType->id}/edit"))->with(['toast' => $toastData]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_jobs_employment_types');

        $employmentType = JobEmploymentType::query()->findOrFail($id);

        $employmentType->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.employment_type_deleted_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/employment-types"))->with(['toast' => $toastData]);
    }

    private function handleExtraData(Request $request, $employmentType)
    {
        $data = $request->all();

        JobEmploymentTypeTranslation::query()->updateOrCreate([
            'job_employment_type_id' => $employmentType->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
        ]);

    }


}
