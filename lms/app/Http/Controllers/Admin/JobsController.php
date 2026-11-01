<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MorphTypesEnum;
use App\Exports\JobsListsExport;
use App\Http\Controllers\Admin\traits\ProductBadgeTrait;
use App\Http\Controllers\Admin\traits\SpecificLocationsTrait;
use App\Http\Controllers\Controller;
use App\Mail\SendNotifications;
use App\Models\Job;
use App\Models\JobSelectedFilterOption;
use App\Models\JobCategory;
use App\Models\JobCategoryFilter;
use App\Models\JobEmploymentType;
use App\Models\JobExperienceLevel;
use App\Models\Notification;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Tag;
use App\Models\Translation\JobTranslation;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class JobsController extends Controller
{
    use ProductBadgeTrait, SpecificLocationsTrait;

    public function index(Request $request)
    {
        $this->authorize("admin_jobs_lists");

        $query = Job::query();

        $topStats = $this->handleTopStats(deepClone($query));

        $jobs = $this->handleFilters($request, $query)
            ->with([
                'category',
                'employmentType',
                'experienceLevel',
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'mobile', 'email', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified');
                },
            ])
            ->withCount([
                'jobRequests'
            ])
            ->paginate(10);

        $employmentTypes = JobEmploymentType::query()->where('enable', true)->get();

        $data = [
            'pageTitle' => trans('update.jobs'),
            'jobs' => $jobs,
            'employmentTypes' => $employmentTypes,
        ];
        $data = array_merge($data, $topStats);

        return view('admin.jobs.lists.index', $data);

    }

    private function handleTopStats(Builder $query): array
    {
        $time = time();

        $totalJobPositions = deepClone($query)->count();

        $totalActiveJobPositions = deepClone($query)->where('status', 'publish')->count();

        $totalDraftJobPositions = deepClone($query)->where('status', 'draft')->count();

        $expirationDaysSetting = getJobsGeneralSettings("jobs_expiration");

        $totalExpiredJobPositions = deepClone($query)
            ->where(function (Builder $query) use ($time, $expirationDaysSetting) {
                $query->where(function (Builder $query) use ($time) {
                    $query->whereNotNull("hiring_end_date");
                    $query->where('hiring_end_date', "<", $time);
                });

                if (!empty($expirationDaysSetting)) {
                    $query->orWhere(function (Builder $query) use ($time, $expirationDaysSetting) {
                        $query->whereNull("hiring_end_date");
                        $query->whereRaw("(({$expirationDaysSetting} * 86400) + jobs.created_at) < {$time}");
                    });
                }
            })
            ->count();

        return [
            'totalJobPositions' => $totalJobPositions,
            'totalActiveJobPositions' => $totalActiveJobPositions,
            'totalDraftJobPositions' => $totalDraftJobPositions,
            'totalExpiredJobPositions' => $totalExpiredJobPositions,
        ];
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $search = $request->get('search');
        $createdDate = $request->get('created_date');
        $employerIds = $request->get('employer_ids', []);
        $employmentTypeId = $request->get('employment_type_id');
        $workArrangement = $request->get('work_arrangement');
        $status = $request->get('status');
        $sort = $request->get('sort');

        $query = fromAndToDateFilter($createdDate, null, $query, 'created_at');

        if (!empty($search)) {
            $query->whereTranslationLike('title', '%' . $search . '%');
        }

        if (!empty($employerIds) and count($employerIds)) {
            $query->whereIn('creator_id', $employerIds);
        }

        if (!empty($employmentTypeId)) {
            $query->where('employment_type_id', $employmentTypeId);
        }

        if (!empty($workArrangement)) {
            $query->where('work_arrangement', $workArrangement);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'create_date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'create_date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'apply_requests_asc':
                    $query->orderBy('job_requests_count', 'asc');
                    break;
                case 'apply_requests_desc':
                    $query->orderBy('job_requests_count', 'desc');
                    break;
                case 'expiry_end_date_asc':
                    $query->orderBy('hiring_end_date', 'asc');
                    break;
                case 'expiry_end_date_desc':
                    $query->orderBy('hiring_end_date', 'desc');
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
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getCreatePageData(): array
    {
        $categories = JobCategory::query()->where('enable', true)
            ->whereNull('parent_id')
            ->with([
                'subCategories' => function ($query) {
                    $query->where('enable', true);
                    $query->orderBy('order', 'asc');
                },
            ])
            ->orderBy('order', 'asc')
            ->get();

        $employmentTypes = JobEmploymentType::query()->where('enable', true)->get();

        $experienceLevels = JobExperienceLevel::query()->where('enable', true)->get();


        return [
            'categories' => $categories,
            'employmentTypes' => $employmentTypes,
            'experienceLevels' => $experienceLevels,
        ];
    }

    public function create(Request $request)
    {
        $this->authorize("admin_jobs_create");

        $data = [
            'pageTitle' => trans('update.new_job')
        ];
        $data = array_merge($data, $this->getCreatePageData());

        return view('admin.jobs.create.index', $data);
    }

    public function store(Request $request)
    {
        $this->authorize("admin_jobs_create");

        $this->validate($request, [
            'title' => 'required|max:255',
            'slug' => 'nullable|max:255|unique:jobs,slug',
            'creator_id' => 'required|exists:users,id',
            'thumbnail' => 'required|max:255',
            'cover_image' => 'required|max:255',
            'seo_description' => 'required|string',
            'summary' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:job_categories,id',
            'company_size' => 'nullable|numeric',
            'employment_type_id' => 'required|exists:job_employment_types,id',
            'experience_level_id' => 'required|exists:job_experience_levels,id',
            'work_arrangement' => 'required|in:on_site,remote,hybrid',
            'hiring_count' => 'required|numeric',
            'payment_type' => 'required|in:fixed_amount,range,negotiable',
            'fixed_salary' => 'required_if:payment_type,fixed_amount|check_price',
            'min_salary' => 'required_if:payment_type,range|check_price',
            'max_salary' => 'required_if:payment_type,range|check_price',
            'payment_period' => 'required|in:hour,day,month,year,project',
        ]);

        $storeData = $this->makeStoreData($request);
        $job = Job::query()->create($storeData);

        $this->handleStoreExtraData($request, $job);

        if ($job->status == 'publish') {
            $createJobReward = RewardAccounting::calculateScore(Reward::CREATE_JOB_BY_EMPLOYER);
            RewardAccounting::makeRewardAccounting($job->creator_id, $createJobReward, Reward::CREATE_BLOG_BY_INSTRUCTOR, $job->id, true);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_created_successfully'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/{$job->id}/edit"))->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->where('id', $id)
            ->with([
                'prerequisites' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'relatedCourses' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'faqs' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'extraDescriptions' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'specificLocation',
            ])
            ->first();

        if (!empty($job)) {
            $locale = mb_strtolower($request->get('locale', getDefaultLocale()));

            $data = [
                'pageTitle' => trans('update.edit_job'),
                'job' => $job,
                'locale' => $locale,
            ];
            $data = array_merge($data, $this->getCreatePageData());

            return view('admin.jobs.create.index', $data);
        }

        abort(404);
    }

    public function update(Request $request, $id)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($id);

        $this->validate($request, [
            'title' => 'required|max:255',
            'slug' => 'nullable|max:255|unique:jobs,slug,' . $job->id,
            'creator_id' => 'required|exists:users,id',
            'thumbnail' => 'required|max:255',
            'cover_image' => 'required|max:255',
            'category_id' => 'required|exists:job_categories,id',
            'seo_description' => 'required|string',
            'summary' => 'required|string',
            'description' => 'required|string',
            'company_size' => 'nullable|numeric',
            'employment_type_id' => 'required|exists:job_employment_types,id',
            'experience_level_id' => 'required|exists:job_experience_levels,id',
            'work_arrangement' => 'required|in:on_site,remote,hybrid',
            'hiring_count' => 'required|numeric',
            'payment_type' => 'required|in:fixed_amount,range,negotiable',
            'fixed_salary' => 'required_if:payment_type,fixed_amount|check_price',
            'min_salary' => 'required_if:payment_type,range|check_price',
            'max_salary' => 'required_if:payment_type,range|check_price',
            'payment_period' => 'required|in:hour,day,month,year,project',
        ]);

        $storeData = $this->makeStoreData($request, $job);
        $job->update($storeData);

        $this->handleStoreExtraData($request, $job);

        if ($job->status == 'publish') {
            $createJobReward = RewardAccounting::calculateScore(Reward::CREATE_JOB_BY_EMPLOYER);
            RewardAccounting::makeRewardAccounting($job->creator_id, $createJobReward, Reward::CREATE_BLOG_BY_INSTRUCTOR, $job->id, true);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_updated_successfully'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs/{$job->id}/edit"))->with(['toast' => $toastData]);
    }

    private function makeStoreData(Request $request, $job = null): array
    {
        $data = $request->all();

        return [
            'slug' => !empty($data['slug']) ? $data['slug'] : Job::makeSlug($data['title']),
            'creator_id' => $data['creator_id'],
            'category_id' => $data['category_id'],
            'thumbnail' => $data['thumbnail'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'icon' => $data['icon'] ?? null,
            'video_demo_source' => (!empty($data['video_demo']) and !empty($data['video_demo_source'])) ? $data['video_demo_source'] : null,
            'video_demo' => $data['video_demo'] ?? null,

            'hiring_end_date' => !empty($data['hiring_end_date']) ? convertTimeToUTCzone($data['hiring_end_date'])->getTimestamp() : null,
            'company_size' => !empty($data['company_size']) ? $data['company_size'] : null,
            'employment_type_id' => !empty($data['employment_type_id']) ? $data['employment_type_id'] : null,
            'experience_level_id' => !empty($data['experience_level_id']) ? $data['experience_level_id'] : null,
            'work_arrangement' => !empty($data['work_arrangement']) ? $data['work_arrangement'] : "on_site",
            'hiring_count' => !empty($data['hiring_count']) ? $data['hiring_count'] : null,
            'payment_type' => !empty($data['payment_type']) ? $data['payment_type'] : "fixed_amount",
            'fixed_salary' => ($data['payment_type'] == "fixed_amount" and !empty($data['fixed_salary'])) ? convertPriceToDefaultCurrency($data['fixed_salary']) : null,
            'min_salary' => ($data['payment_type'] == "range" and !empty($data['min_salary'])) ? convertPriceToDefaultCurrency($data['min_salary']) : null,
            'max_salary' => ($data['payment_type'] == "range" and !empty($data['max_salary'])) ? convertPriceToDefaultCurrency($data['max_salary']) : null,
            'payment_period' => !empty($data['payment_period']) ? $data['payment_period'] : "hour",

            'private' => (!empty($data['private']) and $data['private'] == "on"),
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'created_at' => !empty($job) ? $job->created_at : time(),
            'updated_at' => time(),
        ];
    }

    private function handleStoreExtraData(Request $request, $job)
    {
        $data = $request->all();

        JobTranslation::query()->updateOrCreate([
            'job_id' => $job->id,
            'locale' => mb_strtolower($data['locale']),
        ], [
            'title' => $data['title'],
            'seo_description' => $data['seo_description'] ?? null,
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'important_notes' => $data['important_notes'] ?? null,
        ]);


        // Category Filter Options
        JobSelectedFilterOption::query()->where('job_id', $job->id)->delete();

        $filters = $request->get('filters', null);
        if (!empty($filters) and is_array($filters)) {
            foreach ($filters as $filter) {
                JobSelectedFilterOption::query()->create([
                    'job_id' => $job->id,
                    'filter_option_id' => $filter
                ]);
            }
        }

        // Tags
        Tag::query()->where('job_id', $job->id)->delete();

        if (!empty($request->get('tags'))) {
            $tags = explode(',', $request->get('tags'));

            foreach ($tags as $tag) {
                Tag::query()->create([
                    'job_id' => $job->id,
                    'title' => $tag,
                ]);
            }
        }

        // Product Badge
        $this->handleProductBadges($job, $data);

        // Handle Specific Location
        $locationData = (!empty($data['specificLocation']) and is_array($data['specificLocation'])) ? $data['specificLocation'] : [];
        $this->updateOrCreateSpecificLocation($job->id, MorphTypesEnum::JOB, $locationData);

    }

    public function delete(Request $request, $id)
    {
        $this->authorize("admin_jobs_delete");
        $job = Job::query()->findOrFail($id);

        $job->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_deleted_successfully'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/jobs"))->with(['toast' => $toastData]);
    }

    public function changeStatus($id, $status)
    {
        $this->authorize("admin_jobs_create");
        $job = Job::query()->findOrFail($id);

        $newStatus = $job->status;

        switch ($status) {
            case 'publish':
                $newStatus = 'publish';
                break;
            case 'reject':
                $newStatus = 'rejected';
                break;
            case 'unpublish':
                $newStatus = 'unpublish';
                break;
            case 'cancel':
                $newStatus = 'canceled';
                break;
        }

        $job->update([
            'status' => $newStatus
        ]);

        if ($newStatus == 'publish') {
            $createJobReward = RewardAccounting::calculateScore(Reward::CREATE_JOB_BY_EMPLOYER);
            RewardAccounting::makeRewardAccounting($job->creator_id, $createJobReward, Reward::CREATE_BLOG_BY_INSTRUCTOR, $job->id, true);
        }

        // Notification
        $notifyOptions = [
            '[job_title]' => $job->title,
            '[action]' => trans("update.{$newStatus}"),
            '[time.date]' => dateTimeFormat(time(), 'j M Y'),
        ];
        sendNotification('approve_or_reject_job_for_creator', $notifyOptions, $job->creator_id);


        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_status_changed_successfully'),
            'status' => 'success'
        ];
        return redirect()->back()->with(['toast' => $toastData]);
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        $jobs = Job::select('id')
            ->whereTranslationLike('title', "%$term%")
            ->get();

        $result = [];
        foreach ($jobs as $item) {
            $result[] = [
                'id' => $item->id,
                'title' => $item->title,
            ];
        }

        return response()->json($result, 200);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize("admin_jobs_lists_export_excel");

        $query = Job::query();
        $jobs = $this->handleFilters($request, $query)
            ->with([
                'category',
                'employmentType',
                'experienceLevel',
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'mobile', 'email', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified');
                },
            ])
            ->withCount([
                'jobRequests'
            ])
            ->get();

        $export = new JobsListsExport($jobs);
        return Excel::download($export, 'jobs.xlsx');
    }

    public function notificationToStudents($id)
    {
        $this->authorize('admin_job_send_notification');

        $job = Job::findOrFail($id);

        $data = [
            'pageTitle' => trans('notification.send_notification'),
            'job' => $job
        ];

        return view('admin.jobs.send_notification.index', $data);
    }

    public function sendNotificationToStudents(Request $request, $id)
    {
        $this->authorize('admin_job_send_notification');

        $this->validate($request, [
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        $data = $request->all();

        $job = Job::findOrFail($id);
        $studentsIds = $job->getAllStudentsIds();

        if (count($studentsIds)) {
            foreach ($studentsIds as $studentId) {
                $student = User::query()->select('id', 'full_name', 'role_name', 'role_id', 'mobile', 'email', 'username')
                    ->where('id', $studentId)
                    ->first();

                if (!empty($student)) {
                    Notification::query()->create([
                        'user_id' => $student->id,
                        'group_id' => null,
                        'sender_id' => auth()->id(),
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'sender' => Notification::$AdminSender,
                        'type' => 'single',
                        'created_at' => time()
                    ]);

                    if (!empty($student->email) and env('APP_ENV') == 'production') {
                        \Mail::to($student->email)->send(new SendNotifications(['title' => $data['title'], 'message' => $data['message']]));
                    }
                }
            }

            $toastData = [
                'title' => trans('public.request_success'),
                'msg' => trans('update.the_notification_was_successfully_sent_to_n_students', ['count' => count($studentsIds)]),
                'status' => 'success'
            ];

            return redirect()->back()->with(['toast' => $toastData]);
        }

        $toastData = [
            'title' => trans('public.request_failed'),
            'msg' => trans('update.this_job_has_no_students'),
            'status' => 'error'
        ];

        return redirect()->back()->with(['toast' => $toastData]);
    }


    public function getByCategoryId($categoryId)
    {
        $filters = JobCategoryFilter::query()->where('category_id', $categoryId)
            ->with([
                'options' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ])
            ->get();

        return response()->json([
            'filters' => $filters,
        ], 200);
    }

}
