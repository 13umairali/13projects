<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobResponseReason;
use App\Models\Translation\JobResponseReasonTranslation;
use Illuminate\Http\Request;

class JobsResponseReasonsController extends Controller
{
    public function index()
    {
        $this->authorize('admin_jobs_response_reasons');

        $responseReasons = JobResponseReason::query()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = [
            'pageTitle' => trans('update.response_reasons'),
            'responseReasons' => $responseReasons,
        ];

        return view('admin.jobs.response_reasons.lists', $data);
    }

    public function create(Request $request)
    {
        $this->authorize('admin_jobs_response_reasons');

        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('update.new_response_reason'),
            'locale' => mb_strtolower($locale)
        ];

        return view('admin.jobs.response_reasons.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_jobs_response_reasons');

        $this->validate($request, [
            'title' => 'required|max:255',
            'type' => 'required|in:approve,reject',
        ]);

        $data = $request->all();

        $responseReason = JobResponseReason::query()->create([
            'type' => $data['type'],
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
            'created_at' => time(),
        ]);

        // Extra Data
        $this->handleExtraData($request, $responseReason);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.response_reason_created_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/response-reasons/{$responseReason->id}/edit"))->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_jobs_response_reasons');

        $responseReason = JobResponseReason::query()->findOrFail($id);
        $locale = $request->get('locale', app()->getLocale());

        $data = [
            'pageTitle' => trans('update.edit_response_reason'),
            'responseReason' => $responseReason,
            'locale' => mb_strtolower($locale),
        ];

        return view('admin.jobs.response_reasons.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_jobs_response_reasons');

        $this->validate($request, [
            'title' => 'required|max:255',
            'type' => 'required|in:approve,reject',
        ]);

        $data = $request->all();

        $responseReason = JobResponseReason::query()->findOrFail($id);
        $responseReason->update([
            'type' => $data['type'],
            'enable' => (!empty($data['enable']) and $data['enable'] == 'on'),
        ]);

        // Extra Data
        $this->handleExtraData($request, $responseReason);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.response_reason_updated_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/response-reasons/{$responseReason->id}/edit"))->with(['toast' => $toastData]);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_jobs_response_reasons');

        $responseReason = JobResponseReason::query()->findOrFail($id);

        $responseReason->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.response_reason_deleted_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/response-reasons"))->with(['toast' => $toastData]);
    }

    private function handleExtraData(Request $request, $responseReason)
    {
        $data = $request->all();

        JobResponseReasonTranslation::query()->updateOrCreate([
            'job_response_reason_id' => $responseReason->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
        ]);

    }


}
