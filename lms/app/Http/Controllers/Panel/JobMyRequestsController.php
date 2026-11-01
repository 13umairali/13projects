<?php

namespace App\Http\Controllers\Panel;


use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Job;
use App\Models\JobEmploymentType;
use App\Models\JobRequest;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobMyRequestsController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize("panel_jobs_my_requests");

        $user = auth()->user();

        $query = JobRequest::query()->where('user_id', $user->id);
        $query->join('jobs', 'job_requests.job_id', '=', 'jobs.id');
        $query->select('job_requests.*', 'jobs.payment_type', 'jobs.fixed_salary', 'jobs.min_salary', 'jobs.max_salary');

        $copyQuery = deepClone($query);
        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListsData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $topStats = $this->handleTopStats($copyQuery);
        $jobIds = deepClone($copyQuery)->pluck("job_id")->toArray();
        $employerIds = Job::query()->whereIn("id", $jobIds)->pluck("creator_id")->toArray();
        $employers = User::query()->select("id", 'full_name')->whereIn("id", $employerIds)->get();

        $employmentTypes = JobEmploymentType::query()->where('enable', true)->get();

        $data = [
            'pageTitle' => trans('update.my_requests'),
            'employers' => $employers,
            'employmentTypes' => $employmentTypes,
        ];
        $data = array_merge($data, $topStats);
        $data = array_merge($data, $getListData);

        return view('design_1.panel.jobs.my_requests.index', $data);
    }

    private function handleTopStats(Builder $query): array
    {
        $totalJobRequests = deepClone($query)->count();

        $totalPendingRequests = deepClone($query)->where('job_requests.status', 'pending')->count();

        $totalApprovedRequests = deepClone($query)->where('job_requests.status', 'approved')->count();

        $totalRejectedRequests = deepClone($query)->where('job_requests.status', 'rejected')->count();

        return [
            'totalJobRequests' => $totalJobRequests,
            'totalPendingRequests' => $totalPendingRequests,
            'totalApprovedRequests' => $totalApprovedRequests,
            'totalRejectedRequests' => $totalRejectedRequests,
        ];
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $search = $request->get('search');
        $employerId = $request->get('employer_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $employmentTypeId = $request->get('employment_type_id');
        $status = $request->get('status');
        $sort = $request->get('sort');

        // Date
        $query = fromAndToDateFilter($startDate, $endDate, $query, 'created_at');

        if (!empty($search)) {
            $query->whereHas('job', function ($query) use ($search) {
                $query->whereTranslationLike('title', "%$search%");
            });
        }

        if (!empty($employerId)) {
            $query->whereHas('job', function ($query) use ($employerId) {
                $query->where('creator_id', $employerId);
            });
        }

        if (!empty($employmentTypeId)) {
            $query->whereHas('job', function ($query) use ($employmentTypeId) {
                $query->where('employment_type_id', $employmentTypeId);
            });
        }

        if (!empty($status) and $status != "all") {
            $query->where('job_requests.status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'request_date_asc':
                    $query->orderBy('job_requests.created_at', 'asc');
                    break;
                case 'request_date_desc':
                    $query->orderBy('job_requests.created_at', 'desc');
                    break;
                case 'salary_asc':
                    $query->orderByRaw("
                        CASE
                            WHEN payment_type = 'fixed_amount' THEN fixed_salary
                            WHEN payment_type = 'range' THEN min_salary
                            ELSE 0
                        END ASC
                    ");
                    break;
                case 'salary_desc':
                    $query->orderByRaw("
                        CASE
                            WHEN payment_type = 'fixed_amount' THEN fixed_salary
                            WHEN payment_type = 'range' THEN max_salary
                            ELSE 0
                        END DESC
                    ");
                    break;
            }
        } else {
            $query->orderBy('job_requests.created_at', 'desc');
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
                'job' => function ($query) {
                    $query->with([
                        'employmentType',
                        'experienceLevel',
                        'category',
                        'creator' => function ($query) {
                            $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'email', 'mobile');
                        },
                    ]);
                }
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
            $html .= (string)view()->make("design_1.panel.jobs.my_requests.table_items", ['jobRequest' => $jobRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $jobRequests, $total, $count, true)
        ]);
    }

    public function getEmployerContact($jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->where('user_id', $user->id)
            ->with([
                'job' => function ($query) {
                    $query->with([
                        'creator' => function ($query) {
                            $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'email', 'mobile');
                        },
                    ]);
                }
            ])
            ->first();

        if (!empty($jobRequest)) {
            $html = (string)view()->make("design_1.panel.jobs.modals.user_contact", [
                'userInfo' => $jobRequest->job->creator,
            ]);

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        }

        return response()->json([], 422);
    }

    public function details(Request $request, $jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->where('user_id', $user->id)
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'email', 'mobile');
                },
                'userResume',
                'certificates',
                'learningCourses',
                'attachments',
            ])
            ->first();

        if (!empty($jobRequest)) {

            $data = [
                'pageTitle' => trans('update.job_application_request'),
                'jobRequest' => $jobRequest,
                'job' => $jobRequest->job,
            ];

            return view('design_1.panel.jobs.my_requests.details.index', $data);
        }

        abort(404);
    }


    public function downloadResume(Request $request, $jobRequestId)
    {
        $user = auth()->user();

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->where('user_id', $user->id)
            ->with([
                'userResume'
            ])
            ->first();

        if (!empty($jobRequest) and $jobRequest->userResume) {
            $resume = $jobRequest->userResume;

            $filePath = public_path($resume->attach);

            if (file_exists($filePath)) {
                $fileInfo = pathinfo($filePath);
                $type = (!empty($fileInfo) and !empty($fileInfo['extension'])) ? $fileInfo['extension'] : '';

                $fileName = str_replace(' ', '-', $resume->title);
                $fileName = str_replace('.', '-', $fileName);
                $fileName .= '.' . $type;

                $headers = array(
                    'Content-Type: application/' . $type,
                );

                return response()->download($filePath, $fileName, $headers);
            }
        }

        abort(404);
    }

    public function downloadSharedItem(Request $request, $jobRequestId)
    {
        $user = auth()->user();
        $type = $request->get('type', 'certificate');
        $itemId = $request->get('item');

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
            ->where('user_id', $user->id)
            ->with([
                'certificates',
                'attachments',
            ])
            ->first();

        if (!empty($jobRequest)) {

            if ($type == 'certificate') {
                $certificate = $jobRequest->certificates->where('id', $itemId)->first();

                if (!empty($certificate)) {
                    $makeCertificate = new MakeCertificate();

                    return $makeCertificate->showCertificateByType($certificate);
                }
            } else if ($type == 'attachment') {
                $attachment = $jobRequest->attachments->where('id', $itemId)->first();

                if (!empty($attachment)) {
                    return $this->handleDownloadJobRequestAttachment($attachment);
                }
            }

        }

        abort(404);
    }

    private function handleDownloadJobRequestAttachment($attachment)
    {
        $filePath = public_path($attachment->file);

        if (file_exists($filePath)) {
            $fileInfo = pathinfo($filePath);
            $type = (!empty($fileInfo) and !empty($fileInfo['extension'])) ? $fileInfo['extension'] : '';

            $fileName = str_replace(' ', '-', $attachment->title);
            $fileName = str_replace('.', '-', $fileName);
            $fileName .= '.' . $type;

            $headers = array(
                'Content-Type: application/' . $type,
            );

            return response()->download($filePath, $fileName, $headers);
        }

        abort(404);
    }

}
