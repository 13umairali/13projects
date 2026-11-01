<?php

namespace App\Http\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Gift;
use App\Models\Job;
use App\Models\JobRequest;
use App\Models\JobRequestAttachment;
use App\Models\JobRequestShareItem;
use App\Models\Translation\UserResumeTranslation;
use App\Models\UserResume;
use App\Models\Webinar;
use Illuminate\Http\Request;

class JobRequestController extends Controller
{

    public function index(Request $request, $slug)
    {
        $user = auth()->user();

        if (empty($user)) {
            return redirect('/login');
        }

        $job = Job::query()->where('slug', $slug)
            ->where('status', 'publish')
            ->with([
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified', 'cover_img', 'profile_secondary_image');
                }
            ])
            ->first();

        if (!empty($job)) {

            $checkJobRequest = JobRequest::query()->where('job_id', $job->id)
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!empty($checkJobRequest)) {
                $data = [
                    'pageTitle' => trans('update.job_request_status'),
                    'job' => $job,
                    'jobRequest' => $checkJobRequest,
                ];

                return view('design_1.web.jobs.request_status.index', $data);
            }

            $checkAccess = $this->checkUserCanSendApplicationForm($user, $job);

            if ($checkAccess != "ok") {
                return redirect()->back()->with(['toast' => $checkAccess]);
            }

            $userResumes = $user->resumes;
            $userCertificates = Certificate::query()->where('student_id', $user->id)->get();
            $userCourses = $this->getUserLearningCourses($user);

            $data = [
                'pageTitle' => trans('update.job_application_form'),
                'job' => $job,
                'userResumes' => $userResumes,
                'userCertificates' => $userCertificates,
                'userCourses' => $userCourses,
            ];

            return view('design_1.web.jobs.request.index', $data);
        }

        abort(404);
    }

    private function getUserLearningCourses($user)
    {
        $giftsIds = Gift::query()->where('email', $user->email)
            ->where('status', 'active')
            ->whereNotNull('webinar_id')
            ->where(function ($query) {
                $query->whereNull('date');
                $query->orWhere('date', '<', time());
            })
            ->whereHas('sale')
            ->pluck('id')
            ->toArray();


        return Webinar::query()->where('status', 'active')
            ->whereHas('sales', function ($query) use ($user, $giftsIds) {
                $query->whereNull('refund_at');
                $query->where('access_to_purchased_item', true);

                $query->where(function ($query) use ($user, $giftsIds) {
                    $query->where('sales.buyer_id', $user->id);
                    $query->orWhereIn('sales.gift_id', $giftsIds);
                });
            })
            ->get();
    }

    private function checkUserCanSendApplicationForm($user, $job)
    {
        $expiryDate = $job->getExpiryDate();

        if ($expiryDate <= time()) {
            return [
                'title' => trans('public.request_failed'),
                'msg' => trans('update.cant_send_request_on_expired_job'),
                'status' => 'error'
            ];
        }

        if ($job->creator_id == $user->id) {
            return [
                'title' => trans('public.request_failed'),
                'msg' => trans('update.cant_send_request_on_your_job'),
                'status' => 'error'
            ];
        }


        return "ok";
    }

    public function store(Request $request, $slug)
    {
        $user = auth()->user();

        if (empty($user)) {
            return redirect('/login');
        }

        $this->validate($request, [
            'resume' => 'required',
            'new_resume_title' => 'required_if:resume,new_upload|max:255',
            'new_resume_attach' => 'required_if:resume,new_upload|file',
        ]);

        $job = Job::query()->where('slug', $slug)
            ->where('status', 'publish')
            ->with([
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified', 'cover_img', 'profile_secondary_image');
                }
            ])
            ->first();

        if (!empty($job)) {
            $checkAccess = $this->checkUserCanSendApplicationForm($user, $job);

            if ($checkAccess != "ok") {
                return redirect()->back()->with(['toast' => $checkAccess]);
            }

            $data = $request->all();

            // User Resume
            if ($data['resume'] == "new_upload") {
                $newResume = $this->handleNewUserResume($request, $user);

                if (!empty($newResume)) {
                    $data['resume'] = $newResume->id;
                }
            }

            $jobRequest = JobRequest::query()->create([
                'user_id' => $user->id,
                'job_id' => $job->id,
                'user_resume_id' => $data['resume'],
                'description' => $data['description'] ?? null,
                'status' => 'pending',
                'created_at' => time(),
            ]);

            // Store Extra Data
            $this->handleStoreExtraData($request, $jobRequest);

            // Notification
            $notifyOptions = [
                '[job_title]' => $job->title,
                '[u.name]' => $user->full_name,
                '[time.date]' => dateTimeFormat(time(), 'j M Y'),
            ];
            sendNotification('new_job_request_for_employer', $notifyOptions, $job->creator_id);
            sendNotification('new_job_request_for_applicant', $notifyOptions, $user->id);

            // Show Status stats
            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans('update.submit_job_application_success_msg'),
                'redirect_to' => $job->getRequestUrl(),
            ]);
        }

        abort(404);
    }

    private function handleStoreExtraData(Request $request, $jobRequest)
    {
        $data = $request->all();

        // certificates
        if (!empty($data['certificates']) and is_array($data['certificates'])) {
            $insert = [];

            foreach ($data['certificates'] as $certificateId) {
                if (!empty($certificateId)) {
                    $insert[] = [
                        'job_request_id' => $jobRequest->id,
                        'certificate_id' => $certificateId,
                        'course_id' => null,
                    ];
                }
            }

            if (!empty($insert)) {
                JobRequestShareItem::query()->insert($insert);
            }
        }

        // Learning History
        if (!empty($data['courses']) and is_array($data['courses'])) {
            $insert2 = [];

            foreach ($data['courses'] as $courseId) {
                if (!empty($courseId)) {
                    $insert2[] = [
                        'job_request_id' => $jobRequest->id,
                        'certificate_id' => null,
                        'course_id' => $courseId,
                    ];
                }
            }

            if (!empty($insert2)) {
                JobRequestShareItem::query()->insert($insert2);
            }
        }

        // Attachments
        $attachments = (!empty($data['attachments']) and count($data['attachments'])) ? array_map('array_filter', $data['attachments']) : [];
        if (!empty($attachments)) {
            $attachmentsInsert = [];

            foreach ($attachments as $attachment) {
                if (!empty($attachment['title']) and !empty($attachment['file'])) {

                    $destination = "jobs/requests/{$jobRequest->id}/attachments";

                    $filePath = $this->uploadFile($attachment['file'], $destination, null, $jobRequest->user_id);

                    if (!empty($filePath)) {
                        $attachmentsInsert[] = [
                            'job_request_id' => $jobRequest->id,
                            'title' => $attachment['title'],
                            'file' => $filePath,
                        ];
                    }
                }
            }

            if (!empty($attachmentsInsert)) {
                JobRequestAttachment::query()->insert($attachmentsInsert);
            }
        }
    }

    private function handleNewUserResume(Request $request, $user)
    {
        $locale = $request->get('locale', app()->getLocale());
        $data = $request->all();

        $resume = UserResume::query()->create([
            'user_id' => $user->id,
            'attach' => null,
            'created_at' => time(),
        ]);

        UserResumeTranslation::query()->updateOrCreate([
            'user_resume_id' => $resume->id,
            'locale' => mb_strtolower($locale)
        ], [
            'title' => $data['new_resume_title'],
        ]);


        // Upload File
        $path = $resume->attach ?? null;
        $destination = "setting/resumes/{$resume->id}";

        if (!empty($request->file('new_resume_attach'))) {
            if (!empty($path)) {
                $this->removeFile($path);
            }

            $path = $this->uploadFile($request->file('new_resume_attach'), $destination, null, $user->id);
        }

        $resume->update([
            'attach' => $path
        ]);

        return $resume;
    }
}
