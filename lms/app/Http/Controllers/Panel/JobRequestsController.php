<?php

namespace App\Http\Controllers\Panel;


use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobEmploymentType;
use App\Models\JobRequest;
use App\Models\JobResponseReason;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobRequestsController extends Controller
{

    public function index(Request $request, $jobId)
    {
        $user = auth()->user();

        $job = Job::query()->where('id', $jobId)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($job)) {
            $query = JobRequest::query()->where('job_id', $job->id);

            $copyQuery = deepClone($query);
            $query = $this->handleFilters($request, $query);
            $getListData = $this->getListsData($request, $query);

            if ($request->ajax()) {
                return $getListData;
            }

            $topStats = $this->handleTopStats($copyQuery);

            $data = [
                'pageTitle' => trans('update.application_requests'),
                'job' => $job,
            ];
            $data = array_merge($data, $topStats);
            $data = array_merge($data, $getListData);

            return view('design_1.panel.jobs.requests.lists.index', $data);
        }

        abort(404);
    }

    private function handleTopStats(Builder $query): array
    {
        $totalAppliedUsers = deepClone($query)->count();

        $totalPendingUsers = deepClone($query)->where('status', 'pending')->count();

        $totalApprovedUsers = deepClone($query)->where('status', 'approved')->count();

        $totalRejectedUsers = deepClone($query)->where('status', 'rejected')->count();

        return [
            'totalAppliedUsers' => $totalAppliedUsers,
            'totalPendingUsers' => $totalPendingUsers,
            'totalApprovedUsers' => $totalApprovedUsers,
            'totalRejectedUsers' => $totalRejectedUsers,
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

    private function getListsData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $jobRequests = $query
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'email', 'mobile');
                },
            ])
            ->withCount([
                'certificates',
                'learningCourses',
                'attachments',
            ])
            ->get();


        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $jobRequests, $total, $count);
        }

        return [
            'jobRequests' => $jobRequests,
            'pagination' => $this->makePagination($request, $jobRequests, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $jobRequests, $total, $count)
    {
        $html = "";

        foreach ($jobRequests as $jobRow) {
            $html .= (string)view()->make("design_1.panel.jobs.requests.lists.table_items", ['jobRequest' => $jobRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $jobRequests, $total, $count, true)
        ]);
    }

    public function details(Request $request, $jobId, $jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->whereHas('job', function ($query) use ($jobId, $user) {
                $query->where('id', $jobId);
                $query->where('creator_id', $user->id);
            })
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'email', 'mobile');
                },
                'userResume'
            ])
            ->first();

        if (!empty($jobRequest)) {

            $data = [
                'pageTitle' => trans('update.job_application_request'),
                'jobRequest' => $jobRequest,
                'job' => $jobRequest->job,
            ];

            return view('design_1.panel.jobs.requests.details.index', $data);
        }

        abort(404);
    }

    public function togglePin($jobId, $jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->whereHas('job', function ($query) use ($jobId, $user) {
                $query->where('id', $jobId);
                $query->where('creator_id', $user->id);
            })
            ->first();

        if (!empty($jobRequest)) {
            $pinStatus = !$jobRequest->pinned;

            $jobRequest->update([
                'pinned' => $pinStatus,
            ]);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => $pinStatus ? trans('update.job_request_pinned_successful') : trans('update.job_request_unpinned_successful'),
            ]);
        }

        return response()->json([], 422);
    }

    public function getModerateModal(Request $request, $jobId, $jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->whereHas('job', function ($query) use ($jobId, $user) {
                $query->where('id', $jobId);
                $query->where('creator_id', $user->id);
            })
            ->first();

        if (!empty($jobRequest)) {
            $type = $request->get('type', 'approve');
            if (!in_array($type, ['approve', 'reject'])) {
                $type = "approve";
            }

            $moderateReasons = JobResponseReason::query()->where('type', $type)
                ->where('enable', true)
                ->get();

            $html = (string)view()->make("design_1.panel.jobs.modals.moderate_request_status", [
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

    public function storeModerateStatus(Request $request, $jobId, $jobRequestId)
    {
        $this->validate($request, [
            'type' => 'required|in:approve,reject',
            'reason_id' => 'required|exists:job_response_reasons,id',
            'description' => 'required|string',
        ]);

        $data = $request->all();
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->whereHas('job', function ($query) use ($jobId, $user) {
                $query->where('id', $jobId);
                $query->where('creator_id', $user->id);
            })
            ->first();

        if (!empty($jobRequest)) {
            $jobReason = JobResponseReason::query()->where('id', $data['reason_id'])->first();

            $newStatus = ($data['type'] == 'approve') ? 'approved' : 'rejected';

            $jobRequest->update([
                'moderator_id' => $user->id,
                'job_reason_id' => $data['reason_id'],
                'reason_description' => $data['description'],
                'status' => $newStatus,
            ]);

            // Notification
            $notifyOptions = [
                '[job_title]' => $jobRequest->job->title,
                '[action]' => trans("update.{$newStatus}"),
                '[reason]' => !empty($jobReason) ? $jobReason->title : '-',
                '[u.name]' => $user->full_name,
                '[time.date]' => dateTimeFormat(time(), 'j M Y'),
            ];
            sendNotification('approve_or_reject_job_for_creator', $notifyOptions, $jobRequest->user_id);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans("update.job_request_{$data['type']}_successful"),
            ]);
        }

        return response()->json([], 422);
    }

    public function getUserContact($jobId, $jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->whereHas('job', function ($query) use ($jobId, $user) {
                $query->where('id', $jobId);
                $query->where('creator_id', $user->id);
            })
            ->first();

        if (!empty($jobRequest)) {
            $html = (string)view()->make("design_1.panel.jobs.modals.user_contact", [
                'userInfo' => $jobRequest->user,
            ]);

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        }

        return response()->json([], 422);
    }

}
