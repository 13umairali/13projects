<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\WebinarExtraDescription;
use App\Models\Translation\WebinarExtraDescriptionTranslation;
use Illuminate\Http\Request;

class JobExtraDescriptionController extends Controller
{

    public function getForm(Request $request, $jobId)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);

        $locale = mb_strtolower($request->get('locale', getDefaultLocale()));
        $type = $request->get('type', \App\Models\WebinarExtraDescription::$LEARNING_MATERIALS);

        $html = (string)view()->make('admin.jobs.create.contents.extra_description.form', [
            'job' => $job,
            'locale' => $locale,
            'type' => $type,
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
            'type' => 'required|in:' . implode(',', WebinarExtraDescription::$types),
            'value' => 'required',
        ]);

        $storeData = $this->makeStoreData($request, $job);
        $extraDescription = WebinarExtraDescription::query()->create($storeData);

        $this->handleStoreExtraData($request, $extraDescription);

        return response()->json([
            'code' => 200,
            'title' => trans('public.request_success'),
            'msg' => trans("update.job_{$extraDescription->type}_created_successfully"),
        ]);
    }

    public function edit(Request $request, $jobId, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);
        $extraDescription = WebinarExtraDescription::query()->where('id', $id)
            ->where('job_id', $jobId)
            ->first();

        if (!empty($extraDescription)) {
            $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

            $html = (string)view()->make('admin.jobs.create.contents.extra_description.form', [
                'job' => $job,
                'extraDescription' => $extraDescription,
                'locale' => $locale,
                'type' => $extraDescription->type,
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
        $extraDescription = WebinarExtraDescription::query()->where('id', $id)
            ->where('job_id', $jobId)
            ->first();

        if (!empty($extraDescription)) {
            $this->validate($request, [
                'type' => 'required|in:' . implode(',', WebinarExtraDescription::$types),
                'value' => 'required',
            ]);

            $storeData = $this->makeStoreData($request, $job, $extraDescription);
            $extraDescription->update($storeData);

            $this->handleStoreExtraData($request, $extraDescription);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans("update.job_{$extraDescription->type}_updated_successfully"),
            ]);
        }

        return response()->json([], 422);
    }

    public function delete(Request $request, $jobId, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);
        $extraDescription = WebinarExtraDescription::query()->where('id', $id)
            ->where('job_id', $job->id)
            ->first();

        if (!empty($extraDescription)) {
            $extraDescription->delete();

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans("update.job_{$extraDescription->type}_deleted_successfully"),
                'status' => 'success'
            ];
            return redirect()->back()->with(['toast' => $toastData]);
        }

        abort(404);
    }

    private function makeStoreData(Request $request, $job, $extraDescription = null): array
    {
        $data = $request->all();

        if (empty($data['locale'])) {
            $data['locale'] = getDefaultLocale();
        }

        $order = WebinarExtraDescription::query()->where('job_id', $job->id)
                ->where('type', $data['type'])
                ->count() + 1;

        return [
            'creator_id' => $job->creator_id,
            'job_id' => $job->id,
            'type' => $data['type'],
            'order' => $order,
            'created_at' => !empty($extraDescription) ? $extraDescription->created_at : time(),
        ];
    }

    private function handleStoreExtraData(Request $request, $extraDescription)
    {
        $data = $request->all();
        if (empty($data['locale'])) {
            $data['locale'] = getDefaultLocale();
        }

        WebinarExtraDescriptionTranslation::query()->updateOrCreate([
            'webinar_extra_description_id' => $extraDescription->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'value' => $data['value'],
        ]);


    }
}
