<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Prerequisite;
use Illuminate\Http\Request;

class JobPrerequisitesController extends Controller
{

    public function getForm(Request $request, $jobId)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);

        $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

        $html = (string)view()->make('admin.jobs.create.contents.prerequisites.form', [
            'job' => $job,
            'locale' => $locale,
        ]);

        return response()->json([
            'code' => 200,
            'html' => $html,
        ]);
    }


    public function store(Request $request, $jobId)
    {
        $this->authorize("admin_jobs_create");

        $job = Job::query()->findOrFail($jobId);

        $this->validate($request, [
            'prerequisite' => 'required|unique:prerequisites,prerequisite_id,null,id,job_id,' . $jobId,
        ]);

        $storeData = $this->makeStoreData($request, $job);
        $prerequisite = Prerequisite::query()->create($storeData);

        return response()->json([
            'code' => 200,
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_prerequisite_created_successfully'),
        ]);
    }

    public function edit(Request $request, $jobId, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);
        $prerequisite = Prerequisite::query()->where('id', $id)
            ->where('job_id', $jobId)
            ->first();

        if (!empty($prerequisite)) {
            $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

            $html = (string)view()->make('admin.jobs.create.contents.prerequisites.form', [
                'job' => $job,
                'prerequisite' => $prerequisite,
                'locale' => $locale,
            ]);

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $jobId, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);
        $prerequisite = Prerequisite::query()->where('id', $id)
            ->where('job_id', $jobId)
            ->first();

        if (!empty($prerequisite)) {
            $this->validate($request, [
                'prerequisite' => "required|unique:prerequisites,prerequisite_id,{$id},id,job_id,{$jobId}",
            ]);

            $storeData = $this->makeStoreData($request, $job, $prerequisite);
            $prerequisite->update($storeData);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans('update.job_prerequisite_updated_successfully'),
            ]);
        }

        return response()->json([], 422);
    }

    public function delete(Request $request, $jobId, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);
        $prerequisite = Prerequisite::query()->where('id', $id)
            ->where('job_id', $job->id)
            ->first();

        if (!empty($prerequisite)) {
            $prerequisite->delete();

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.job_prerequisite_deleted_successfully'),
                'status' => 'success'
            ];
            return redirect()->back()->with(['toast' => $toastData]);
        }

        abort(404);
    }

    private function makeStoreData(Request $request, $job, $prerequisite = null): array
    {
        $data = $request->all();

        return [
            'webinar_id' => null,
            'job_id' => $job->id,
            'prerequisite_id' => $data['prerequisite'],
            'required' => (!empty($data['required']) and $data['required'] == 'on'),
            'created_at' => !empty($prerequisite) ? $prerequisite->created_at : time(),
        ];
    }


}
