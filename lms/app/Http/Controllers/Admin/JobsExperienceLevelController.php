<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobExperienceLevel;
use App\Models\Translation\JobExperienceLevelTranslation;
use Illuminate\Http\Request;

class JobsExperienceLevelController extends Controller
{
    public function index()
    {
        $this->authorize('admin_jobs_experience_levels');

        $experienceLevels = JobExperienceLevel::query()
            ->withCount([
                'jobs'
            ])
            ->paginate(10);

        $data = [
            'pageTitle' => trans('update.experience_levels'),
            'experienceLevels' => $experienceLevels,
        ];

        return view('admin.jobs.experience_levels.lists', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('admin_jobs_experience_levels');

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('update.new_experience_level'),
            'locale' => mb_strtolower($locale)
        ];

        return view('admin.jobs.experience_levels.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_jobs_experience_levels');

        $this->validate($request, [
            'title' => 'required|max:255',
            'icon' => 'required',
        ]);

        $data = $request->all();

        $experienceLevel = JobExperienceLevel::query()->create([
            'icon' => $data['icon'],
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
        ]);

        // Extra Data
        $this->handleExtraData($request, $experienceLevel);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.experience_level_created_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/experience-levels/{$experienceLevel->id}/edit"))->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_jobs_experience_levels');

        $experienceLevel = JobExperienceLevel::query()->findOrFail($id);
        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('update.edit_experience_level'),
            'experienceLevel' => $experienceLevel,
            'locale' => mb_strtolower($locale),
        ];

        return view('admin.jobs.experience_levels.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_jobs_experience_levels');

        $this->validate($request, [
            'title' => 'required|max:255',
            'icon' => 'required',
        ]);

        $data = $request->all();

        $experienceLevel = JobExperienceLevel::query()->findOrFail($id);
        $experienceLevel->update([
            'icon' => $data['icon'],
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
        ]);

        // Extra Data
        $this->handleExtraData($request, $experienceLevel);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.experience_level_updated_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/experience-levels/{$experienceLevel->id}/edit"))->with(['toast' => $toastData]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_jobs_experience_levels');

        $experienceLevel = JobExperienceLevel::query()->findOrFail($id);

        $experienceLevel->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.experience_level_deleted_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/experience-levels"))->with(['toast' => $toastData]);
    }

    private function handleExtraData(Request $request, $experienceLevel)
    {
        $data = $request->all();

        JobExperienceLevelTranslation::query()->updateOrCreate([
            'job_experience_level_id' => $experienceLevel->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
        ]);

    }


}
