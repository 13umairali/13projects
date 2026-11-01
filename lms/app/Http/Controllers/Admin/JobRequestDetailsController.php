<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Mixins\Certificate\MakeCertificate;
use App\Models\Job;
use App\Models\JobRequest;
use App\Models\JobResponseReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobRequestDetailsController extends Controller
{

    public function index(Request $request, $jobRequestId)
    {
        $this->authorize("admin_jobs_requests_details");

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
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

            return view('admin.jobs.requests.details.index', $data);
        }

        abort(404);
    }


    public function downloadResume(Request $request, $jobRequestId)
    {
        $this->authorize("admin_jobs_requests_details");

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
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
        $this->authorize("admin_jobs_requests_details");

        $type = $request->get('type', 'certificate');
        $itemId = $request->get('item');

        $jobRequest = JobRequest::query()->where('id', $jobRequestId)
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
