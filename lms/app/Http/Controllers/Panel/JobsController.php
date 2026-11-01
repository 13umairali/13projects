<?php

namespace App\Http\Controllers\Panel;

use App\Enums\MorphTypesEnum;
use App\Enums\UploadSource;
use App\Http\Controllers\Admin\traits\SpecificLocationsTrait;
use App\Http\Controllers\Controller;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobCategoryFilter;
use App\Models\JobEmploymentType;
use App\Models\JobExperienceLevel;
use App\Models\JobSelectedFilterOption;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Tag;
use App\Models\Translation\JobTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobsController extends Controller
{
    use SpecificLocationsTrait;

    public function index(Request $request)
    {
        $this->authorize("panel_jobs_lists");

        $user = auth()->user();

        $query = Job::query()->where('creator_id', $user->id);

        $copyQuery = deepClone($query);
        $getListData = $this->getListsData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $topStats = $this->handleTopStats($copyQuery);


        $data = [
            'pageTitle' => trans('update.my_jobs'),
        ];
        $data = array_merge($data, $topStats);
        $data = array_merge($data, $getListData);

        return view('design_1.panel.jobs.my_jobs.index', $data);
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

    private function getListsData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $jobs = $query
            ->with([
                'category',
                'jobRequests' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->withCount([
                'visits'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        /*foreach ($jobs as $job) {

        }*/

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $jobs, $total, $count);
        }

        return [
            'jobs' => $jobs,
            'pagination' => $this->makePagination($request, $jobs, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $jobs, $total, $count)
    {
        $html = "";

        foreach ($jobs as $jobRow) {
            $html .= (string)view()->make("design_1.panel.jobs.my_jobs.job_card.index", ['job' => $jobRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $jobs, $total, $count, true)
        ]);
    }

    private function getCreateWizardStepCount()
    {
        $steps = 6;

        if (!empty(getGeneralOptionsSettings('direct_publication_of_jobs'))) {
            $steps -= 1;
        }

        return $steps;
    }

    public function create()
    {
        $this->authorize("panel_jobs_create");

        $user = auth()->user();

        $userPackage = new UserPackage($user);
        $userJobsCountLimited = $userPackage->checkPackageLimit('jobs_count');

        if ($userJobsCountLimited) {
            session()->put('registration_package_limited', $userJobsCountLimited);

            return redirect()->back();
        }


        $stepCount = $this->getCreateWizardStepCount();

        $data = [
            'pageTitle' => trans('update.new_job'),
            'currentStep' => 1,
            'stepCount' => $stepCount,
        ];

        return view('design_1.panel.jobs.create.index', $data);
    }

    public function store(Request $request)
    {
        $this->authorize("panel_jobs_create");

        $user = auth()->user();

        $userPackage = new UserPackage($user);
        $userJobsCountLimited = $userPackage->checkPackageLimit('jobs_count');

        if ($userJobsCountLimited) {
            session()->put('registration_package_limited', $userJobsCountLimited);

            return redirect()->back();
        }


        $this->validate($request, [
            'locale' => 'required',
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'cover_image' => 'required',
            'seo_description' => 'required|string',
            'summary' => 'required|string',
            'description' => 'required|string',
        ]);

        $data = $request->all();

        $job = Job::query()->create([
            'slug' => Job::makeSlug($data['title']),
            'creator_id' => $user->id,
            'status' => ((!empty($data['draft']) and $data['draft'] == 1) or (!empty($data['get_next']) and $data['get_next'] == 1)) ? 'draft' : 'pending',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Handle Image and Video
        $job = $this->storeJobMedia($request, $job);

        $this->handleStepOneTranslatesData($request, $job, $user);

        $notifyOptions = [
            '[u.name]' => $user->full_name,
            '[item_title]' => $job->title,
            '[content_type]' => trans('update.job'),
        ];
        sendNotification("new_item_created", $notifyOptions, 1);


        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_created_successfully'),
            'status' => 'success'
        ];
        return redirect("/panel/jobs/{$job->id}/step/2")->with(['toast' => $toastData]);
    }

    public function edit(Request $request, $jobId, $step = 1)
    {
        $this->authorize("panel_jobs_create");

        $stepCount = $this->getCreateWizardStepCount();

        if ($step > $stepCount) {
            return redirect("/panel/jobs/{$jobId}/step/{$stepCount}");
        }


        $user = auth()->user();

        $jobQuery = Job::query()->where('id', $jobId)->where('creator_id', $user->id);

        if ($step == 3) {
            $jobQuery->with([
                'specificLocation',
            ]);
        } else if ($step == 4) {
            $jobQuery->with([
                'prerequisites' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'relatedCourses' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ]);
        } else if ($step == 5) {
            $jobQuery->with([
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'extraDescriptions'
            ]);
        }

        $job = $jobQuery->first();

        if (!empty($job)) {
            $locale = $request->get('locale', app()->getLocale());
            $stepCount = $this->getCreateWizardStepCount();


            $data = [
                'pageTitle' => trans('public.edit') . ' | ' . $job->title,
                'currentStep' => $step,
                'stepCount' => $stepCount,
                'locale' => mb_strtolower($locale),
                'job' => $job,
            ];


            if ($step == 2) {
                $data['jobCategories'] = JobCategory::query()->where('enable', true)
                    ->whereNull('parent_id')
                    ->with([
                        'subCategories' => function ($query) {
                            $query->where('enable', true);
                            $query->orderBy('order', 'asc');
                        },
                    ])
                    ->orderBy('order', 'asc')
                    ->get();

                $data['jobTags'] = $job->tags->pluck('title')->toArray();

                $jobCategoryFilters = !empty($job->category) ? $job->category->filters : [];

                if (empty($job->category) and !empty($request->old('category_id'))) {
                    $category = JobCategory::query()->where('id', $request->old('category_id'))->first();

                    if (!empty($category)) {
                        $jobCategoryFilters = $category->filters;
                    }
                }

                $data['jobCategoryFilters'] = $jobCategoryFilters;
            } else if ($step == 3) {
                $data['employmentTypes'] = JobEmploymentType::query()->where('enable', true)->get();
                $data['experienceLevels'] = JobExperienceLevel::query()->where('enable', true)->get();
            }

            return view('design_1.panel.jobs.create.index', $data);
        }

        abort(404);
    }

    public function update(Request $request, $jobId)
    {
        $this->authorize("panel_jobs_create");

        $user = auth()->user();

        $job = Job::query()->where('id', $jobId)
            ->where('creator_id', $user->id)
            ->first();

        if (empty($job)) {
            abort(404);
        }

        $data = $request->all();
        $rules = [];
        $currentStep = $data['current_step'];
        $getStep = $data['get_step'];
        $getNextStep = (!empty($data['get_next']) and $data['get_next'] == 1);
        $isDraft = (!empty($data['draft']) and $data['draft'] == 1);
        $stepCount = $this->getCreateWizardStepCount();

        if ($currentStep == 1) {
            $rules = [
                'title' => 'required|max:255',
                'seo_description' => 'required|string',
                'summary' => 'required|string',
                'description' => 'required|string',
            ];
        } else if ($currentStep == 2) {
            $rules = [
                'category_id' => 'required|exists:job_categories,id',
                'company_size' => 'nullable|numeric',
            ];
        } else if ($currentStep == 3) {
            $rules = [
                'employment_type_id' => 'required|exists:job_employment_types,id',
                'experience_level_id' => 'required|exists:job_experience_levels,id',
                'work_arrangement' => 'required|in:on_site,remote,hybrid',
                'hiring_count' => 'required|numeric',
                'payment_type' => 'required|in:fixed_amount,range,negotiable',
                'fixed_salary' => 'required_if:payment_type,fixed_amount|check_price',
                'min_salary' => 'required_if:payment_type,range|check_price',
                'max_salary' => 'required_if:payment_type,range|check_price',
                'payment_period' => 'required|in:hour,day,month,year,project',
            ];
        }

        $this->validate($request, $rules);

        $directPublication = !empty(getGeneralOptionsSettings('direct_publication_of_jobs'));
        $jobRulesRequired = false;

        if (!$directPublication and (($currentStep == $stepCount and !$getNextStep and !$isDraft) or (!$getNextStep and !$isDraft))) {
            $jobRulesRequired = empty($data['rules']);
        }

        $status = ($isDraft or $jobRulesRequired) ? 'draft' : 'pending';

        if ($directPublication and !$getNextStep and !$isDraft) {
            $status = 'publish';
        }

        $data['status'] = $status;

        if ($currentStep == 1) {
            // Handle Image and Video
            $job = $this->storeJobMedia($request, $job);

            // Translates
            $this->handleStepOneTranslatesData($request, $job, $user);
        } else if ($currentStep == 2) {

            $data['hiring_end_date'] = !empty($data['hiring_end_date']) ? convertTimeToUTCzone($data['hiring_end_date'])->getTimestamp() : null;
            $data['private'] = (!empty($data['private']) and $data['private'] == "on");


            // Filters & Tags
            $this->handleCategoryFiltersAndTags($request, $job);
        } else if ($currentStep == 3) {

            $data['fixed_salary'] = ($data['payment_type'] == "fixed_amount" and !empty($data['fixed_salary'])) ? convertPriceToDefaultCurrency($data['fixed_salary']) : null;
            $data['min_salary'] = ($data['payment_type'] == "range" and !empty($data['min_salary'])) ? convertPriceToDefaultCurrency($data['min_salary']) : null;
            $data['max_salary'] = ($data['payment_type'] == "range" and !empty($data['max_salary'])) ? convertPriceToDefaultCurrency($data['max_salary']) : null;


            // Handle Specific Location
            $locationData = (!empty($data['specificLocation']) and is_array($data['specificLocation'])) ? $data['specificLocation'] : [];
            $this->updateOrCreateSpecificLocation($job->id, MorphTypesEnum::JOB, $locationData);
        } else if ($currentStep == 5) {
            $extraDescriptionController = (new WebinarExtraDescriptionController());
            $extraDescriptionController->storeCompanyLogos($request, 'job_id', $job->id, 'jobs');
        }

        unset($data['_token'],
            $data['current_step'],
            $data['draft'],
            $data['get_step'],
            $data['get_next'],
            $data['tags'],
            $data['filters'],
            $data['ajax'],
            $data['locale'],
            $data['title'],
            $data['subtitle'],
            $data['seo_description'],
            $data['summary'],
            $data['description'],
        );

        $data['updated_at'] = time();
        $job->update($data);

        $url = '/panel/jobs';

        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;

            $url = '/panel/jobs/' . $job->id . '/step/' . (($nextStep <= $stepCount) ? $nextStep : $stepCount);
        }

        if ($status != "publish" and !$getNextStep and !$isDraft and !$jobRulesRequired) {
            $notifyOptions = [
                '[u.name]' => $user->full_name,
                '[item_title]' => $job->title,
                '[content_type]' => trans('update.job'),
            ];
            sendNotification("content_review_request", $notifyOptions, 1);
        }

        if ($status == 'publish') {
            $createJobReward = RewardAccounting::calculateScore(Reward::CREATE_JOB_BY_EMPLOYER);
            RewardAccounting::makeRewardAccounting($user->id, $createJobReward, Reward::CREATE_BLOG_BY_INSTRUCTOR, $job->id, true);
        }

        if ($jobRulesRequired) {
            $url = "/panel/jobs/{$job->id}/step/{$stepCount}";

            return redirect($url)->withErrors(['rules' => trans('validation.required', ['attribute' => 'rules'])]);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_updated_successfully'),
            'status' => 'success'
        ];
        return redirect($url)->with(['toast' => $toastData]);
    }

    public function delete(Request $request, $jobId)
    {
        $this->authorize("panel_jobs_create");

        if (!canDeleteContentDirectly()) {
            if ($request->ajax()) {
                return response()->json([], 422);
            } else {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.it_is_not_possible_to_delete_the_content_directly'),
                    'status' => 'error'
                ];
                return redirect()->back()->with(['toast' => $toastData]);
            }
        }

        $user = auth()->user();

        $job = Job::where('id', $jobId)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($job)) {
            $job->delete();
        }

        return response()->json([
            'code' => 200,
            'title' => trans('public.request_success'),
            'msg' => trans('update.job_deleted_successfully'),
        ]);
    }

    private function handleStepOneTranslatesData(Request $request, $job, $user)
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
    }

    private function handleCategoryFiltersAndTags(Request $request, $job)
    {
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
    }

    protected function storeJobMedia(Request $request, $job)
    {
        $thumbnail = $job->thumbnail ?? null;
        $imageCover = $job->cover_image ?? null;
        $iconPath = $job->icon ?? null;
        $videoDemoSource = $job->video_demo_source ?? null;
        $videoDemo = $job->video_demo ?? null;


        if (!empty($request->file('thumbnail'))) {
            $thumbnail = $this->uploadFile($request->file('thumbnail'), "jobs/{$job->id}", 'thumbnail', $job->creator_id);
        }

        if (!empty($request->file('cover_image'))) {
            $imageCover = $this->uploadFile($request->file('cover_image'), "jobs/{$job->id}", 'cover_image', $job->creator_id);
        }

        if (!empty($request->file('icon'))) {
            $iconPath = $this->uploadFile($request->file('icon'), "jobs/{$job->id}", 'icon', $job->creator_id);
        }


        if (in_array($request->get('video_demo_source'), UploadSource::urlPathItems) and !empty($request->get('demo_video_path'))) {
            $videoDemoSource = $request->get('video_demo_source');
            $videoDemo = $request->get('demo_video_path');
        } elseif ($request->get('video_demo_source') == UploadSource::UPLOAD and !empty($request->file('demo_video_local'))) {
            $videoDemoSource = UploadSource::UPLOAD;
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "jobs/{$job->id}", 'video', $job->creator_id);
        } elseif ($request->get('video_demo_source') == UploadSource::S3 and !empty($request->file('demo_video_local'))) {
            $videoDemoSource = UploadSource::S3;
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "jobs/{$job->id}", 'video', $job->creator_id, 'minio');
        } elseif ($request->get('video_demo_source') == UploadSource::SECURE_HOST and !empty($request->file('demo_video_local'))) {
            $videoDemoSource = UploadSource::SECURE_HOST;
            $videoDemo = $this->uploadFile($request->file('demo_video_local'), "jobs/{$job->id}", "job_{$job->id}_video_demo", $job->creator_id, 'bunny');
        }

        $job->update([
            'thumbnail' => $thumbnail,
            'cover_image' => $imageCover,
            'icon' => $iconPath,
            'video_demo_source' => $videoDemoSource,
            'video_demo' => $videoDemo,
        ]);

        return $job;
    }

    public function getContentItemByLocale(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'item_id' => 'required',
            'locale' => 'required',
            'relation' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $job = Job::query()->where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (!empty($job)) {

            $itemId = $data['item_id'];
            $locale = $data['locale'];
            $relation = $data['relation'];

            if (!empty($job->$relation)) {
                $item = $job->$relation->where('id', $itemId)->first();

                if (!empty($item)) {
                    foreach ($item->translatedAttributes as $attribute) {
                        try {
                            $item->$attribute = $item->translate(mb_strtolower($locale))->$attribute;
                        } catch (\Exception $e) {
                            $item->$attribute = null;
                        }
                    }

                    return response()->json([
                        'item' => $item
                    ], 200);
                }
            }
        }

        abort(403);
    }

    public function getByCategoryId($categoryId)
    {
        $filters = JobCategoryFilter::query()->select('*')
            ->where('category_id', $categoryId)
            ->with([
                'options' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
            ])
            ->get();

        $html = (string)view()->make('design_1.panel.jobs.create.includes.category_filters', [
            'jobCategoryFilters' => $filters,
            'jobFilterOptions' => [],
        ]);

        return response()->json([
            'code' => 200,
            'html' => $html
        ]);
    }

    public function search(Request $request)
    {
        $term = $request->get('term', null);
        $jobId = $request->get('job_id', null);
        $option = $request->get('option', null);

        if (!empty($term)) {
            $query = Job::query()->select('id', 'creator_id')
                ->whereTranslationLike('title', '%' . $term . '%')
                ->where('id', '<>', $jobId)
                ->with([
                    'creator' => function ($query) {
                        $query->select('id', 'full_name');
                    }
                ]);
            //->where('creator_id', $user->id)
            //->get();

            $jobs = $query->get();

            $result = [];

            foreach ($jobs as $job) {
                $result[] = [
                    'id' => $job->id,
                    'title' => $job->title . ' - ' . $job->creator->full_name,
                ];
            }

            return response()->json($result, 200);
        }

        return response('', 422);
    }

}
