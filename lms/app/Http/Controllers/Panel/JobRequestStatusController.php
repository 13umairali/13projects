<?php

namespace App\Http\Controllers\Panel;


use App\Http\Controllers\Controller;
use App\Models\JobRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobRequestStatusController extends Controller
{

    public function index(Request $request, $jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->where('user_id', $user->id)
            ->with([
                'jobResponseReason'
            ])
            ->first();

        if (!empty($jobRequest)) {

            $data = [
                'pageTitle' => trans('update.job_request_status'),
                'job' => $jobRequest->job,
                'jobRequest' => $jobRequest,
            ];

            return view('design_1.web.jobs.request_status.index', $data);
        }

        abort(404);
    }
}
