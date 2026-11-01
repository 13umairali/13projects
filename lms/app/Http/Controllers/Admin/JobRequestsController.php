<?php

namespace App\Http\Controllers\Admin;


use App\Exports\JobsRequestsListsExport;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobRequest;
use App\Models\JobResponseReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class JobRequestsController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize("admin_jobs_requests_history");

        $jobId = $request->get('job_id');
        $query = JobRequest::query();

        $jobsIds = deepClone($query)->pluck('job_id')->toArray();

        if (!empty($jobId)) {
            $query->where('job_id', $jobId);
        }

        $topStats = $this->handleTopStats(deepClone($query));

        $jobRequests = $this->handleFilters($request, $query)
            ->with([
                'job',
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'mobile', 'email', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified');
                },
            ])
            ->paginate(10);

        $filterJobs = Job::query()->whereIn('id', $jobsIds)
            ->select('id', 'creator_id')
            ->get();

        $data = [
            'pageTitle' => trans('update.apply_requests'),
            'jobRequests' => $jobRequests,
            'filterJobs' => $filterJobs,
        ];
        $data = array_merge($data, $topStats);

        return view('admin.jobs.requests.lists.index', $data);
    }

    private function handleTopStats(Builder $query): array
    {
        $totalAppliedRequests = deepClone($query)->count();

        $totalPendingRequests = deepClone($query)->where('status', 'pending')->count();

        $totalApprovedRequests = deepClone($query)->where('status', 'approved')->count();

        $totalRejectedRequests = deepClone($query)->where('status', 'rejected')->count();

        return [
            'totalAppliedRequests' => $totalAppliedRequests,
            'totalPendingRequests' => $totalPendingRequests,
            'totalApprovedRequests' => $totalApprovedRequests,
            'totalRejectedRequests' => $totalRejectedRequests,
        ];
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $sort = $request->get('sort');


        if (!empty($search)) {
            $query->whereHas('job', function ($query) use ($search) {
                $query->whereTranslationLike('title', "%$search%");
            });
        }

        if (!empty($status) and $status != "all") {
            $query->where('status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'request_date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'request_date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('pinned', 'desc');
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function delete(Request $request, $jobRequestId)
    {
        $this->authorize("admin_jobs_requests_history");

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)->first();

        if (!empty($jobRequest)) {
            $jobRequest->delete();

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.job_request_deleted_successfully'),
                'status' => 'success'
            ];
            return redirect()->back()->with(['toast' => $toastData]);
        }

        abort(404);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize("admin_jobs_requests_history");

        $query = JobRequest::query();

        $jobId = $request->get('job_id');
        if (!empty($jobId)) {
            $query->where('job_id', $jobId);
        }

        $jobRequests = $this->handleFilters($request, $query)
            ->with([
                'job',
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'mobile', 'email', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified');
                },
            ])
            ->get();

        $export = new JobsRequestsListsExport($jobRequests);
        return Excel::download($export, 'jobs_requests.xlsx');
    }

    public function getModerateModal(Request $request, $jobRequestId)
    {
        $this->authorize("admin_jobs_requests_history");

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)->first();

        if (!empty($jobRequest)) {
            $type = $request->get('type', 'approve');
            if (!in_array($type, ['approve', 'reject'])) {
                $type = "approve";
            }

            $moderateReasons = JobResponseReason::query()->where('type', $type)
                ->where('enable', true)
                ->get();

            $html = (string)view()->make("admin.jobs.modals.moderate_request_status", [
                'jobRequest' => $jobRequest,
                'moderateReasons' => $moderateReasons,
                'type' => $type,
            ]);

            return response()->json([
                'code' => 200,
                'html' => $html,
                'btn_text' => trans("update.{$type}"),
                'type' => $type
            ]);
        }

        return response()->json([], 422);
    }

    public function storeModerateStatus(Request $request, $jobRequestId)
    {
        $this->validate($request, [
            'type' => 'required|in:approve,reject',
            'reason_id' => 'required|exists:job_response_reasons,id',
            'description' => 'required|string',
        ]);

        $data = $request->all();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)->first();

        if (!empty($jobRequest)) {
            $jobRequest->update([
                'moderator_id' => auth()->id(),
                'job_reason_id' => $data['reason_id'],
                'reason_description' => $data['description'],
                'status' => ($data['type'] == 'approve') ? 'approved' : 'rejected',
            ]);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans("update.job_request_{$data['type']}_successful"),
            ]);
        }

        return response()->json([], 422);
    }

}
