<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Faq;
use App\Models\Translation\FaqTranslation;
use Illuminate\Http\Request;

class JobFaqsController extends Controller
{

    public function getForm(Request $request, $jobId)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);

        $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

        $html = (string)view()->make('admin.jobs.create.contents.faqs.form', [
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
            'title' => 'required|max:255',
            'answer' => 'required',
        ]);

        $storeData = $this->makeStoreData($request, $job);
        $faq = Faq::query()->create($storeData);

        $this->handleStoreExtraData($request, $faq);

        return response()->json([
            'code' => 200,
            'title' => trans('public.request_success'),
            'msg' => trans('update.faq_created_successfully'),
        ]);
    }

    public function edit(Request $request, $jobId, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);
        $faq = Faq::query()->where('id', $id)
            ->where('job_id', $jobId)
            ->first();

        if (!empty($faq)) {
            $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

            $html = (string)view()->make('admin.jobs.create.contents.faqs.form', [
                'job' => $job,
                'faq' => $faq,
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
        $faq = Faq::query()->where('id', $id)
            ->where('job_id', $jobId)
            ->first();

        if (!empty($faq)) {
            $this->validate($request, [
                'title' => 'required|max:255',
                'answer' => 'required',
            ]);

            $storeData = $this->makeStoreData($request, $job, $faq);
            $faq->update($storeData);

            $this->handleStoreExtraData($request, $faq);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans('update.faq_updated_successfully'),
            ]);
        }

        return response()->json([], 422);
    }

    public function delete(Request $request, $jobId, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($jobId);
        $faq = Faq::query()->where('id', $id)
            ->where('job_id', $job->id)
            ->first();

        if (!empty($faq)) {
            $faq->delete();

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.faq_deleted_successfully'),
                'status' => 'success'
            ];
            return redirect()->back()->with(['toast' => $toastData]);
        }

        abort(404);
    }

    private function makeStoreData(Request $request, $job, $faq = null): array
    {
        $order = Faq::query()->where('job_id', $job->id)
                ->count() + 1;

        return [
            'creator_id' => $job->creator_id,
            'job_id' => $job->id,
            'order' => $order,
            'created_at' => !empty($faq) ? $faq->created_at : time(),
        ];
    }

    private function handleStoreExtraData(Request $request, $faq)
    {
        $data = $request->all();

        FaqTranslation::query()->updateOrCreate([
            'faq_id' => $faq->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
            'answer' => $data['answer'],
        ]);


    }
}
