<?php

namespace App\Http\Controllers\Web;


use App\Enums\MorphTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Mixins\Logs\VisitLogMixin;
use App\Models\AdvertisingBanner;
use App\Models\Favorite;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobEmploymentType;
use App\Models\JobExperienceLevel;
use App\Models\JobReport;
use App\Models\JobRequest;
use App\Models\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobsController extends Controller
{
    use CheckContentLimitationTrait;


    public function index(Request $request)
    {
        $query = Job::query()->where('status', 'publish');
        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $jobCategories = JobCategory::query()->where('enable', true)
            ->whereNull('parent_id')
            ->with([
                'subCategories' => function ($query) {
                    $query->where('enable', true);
                    $query->orderBy('order', 'asc');
                },
            ])
            ->orderBy('order', 'asc')
            ->get();

        $seoSettings = getSeoMetas('jobs_lists');
        $pageTitle = $seoSettings['title'] ?? trans('update.jobs');
        $pageDescription = $seoSettings['description'] ?? '';
        $pageRobot = getPageRobot('jobs_lists');

        $filterMaxPrice = 0;

        $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
            ->where('type', Region::$country)
            ->get();

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'jobCategories' => $jobCategories,
            'seoSettings' => $seoSettings,
            'pageBasePath' => "/jobs",
            'filterMaxPrice' => $filterMaxPrice,
            'featuredJobs' => $this->getFeaturedJobs(),
            'employmentTypes' => JobEmploymentType::query()->where('enable', true)->get(),
            'experienceLevels' => JobExperienceLevel::query()->where('enable', true)->get(),
            'countries' => $countries,
        ];


        $data = array_merge($data, $getListData);

        return view('design_1.web.jobs.lists.index', $data);
    }

    private function getFeaturedJobs()
    {
        $result = collect();
        $jobIds = getJobsFeaturedSettings("featured_jobs");

        if (!empty($jobIds) and is_array($jobIds) and count($jobIds)) {
            $result = Job::query()->whereIn('id', $jobIds)
                ->where('status', 'publish')
                ->get();
        }

        return $result;
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $employmentType = $request->get('employment_type');
        $experienceLevel = $request->get('experience_level');
        $workArrangement = $request->get('work_arrangement');
        $countryId = $request->get('country_id');
        $provinceId = $request->get('province_id');
        $cityId = $request->get('city_id');
        $moreOptions = $request->get('more_options');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $employerType = $request->get('employer_type');
        $employerId = $request->get('employer');
        $openPositionsOnly = $request->get('open_positions_only');
        $sort = $request->get('sort');

        if (!empty($search)) {
            $query->whereTranslationLike('title', '%' . $search . '%');
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($employmentType)) {
            $query->where('employment_type_id', $employmentType);
        }

        if (!empty($experienceLevel)) {
            $query->where('experience_level_id', $experienceLevel);
        }

        if (!empty($workArrangement)) {
            $query->where('work_arrangement', $workArrangement);
        }

        if (!empty($countryId)) {
            $query->whereHas('specificLocation', function ($query) use ($countryId) {
                $query->where('country_id', $countryId);
            });
        }

        if (!empty($provinceId)) {
            $query->whereHas('specificLocation', function ($query) use ($provinceId) {
                $query->where('province_id', $provinceId);
            });
        }

        if (!empty($cityId)) {
            $query->whereHas('specificLocation', function ($query) use ($cityId) {
                $query->where('city_id', $cityId);
            });
        }

        if (!empty($moreOptions)) {
            if ($moreOptions == "featured_jobs") {

            } else if ($moreOptions == "negotiable_salary") {
                $query->where('payment_type', 'negotiable');
            }
        }

        if (!empty($minPrice)) {
            $query->where(function ($query) use ($minPrice) {
                $query->where('fixed_salary', '>', $minPrice);
                $query->orWhere('min_salary', '>', $minPrice);
            });
        }

        if (!empty($maxPrice)) {
            $query->where(function ($query) use ($maxPrice) {
                $query->where('fixed_salary', '<=', $maxPrice);
                $query->orWhere('min_salary', '<=', $maxPrice);
            });
        }

        if (!empty($employerType) and $employerType != "all") {
            $query->whereHas('creator', function ($query) use ($employerType) {
                $query->where('role_name', $employerType);
            });
        }

        if (!empty($employerId) and $employerId != "all") {
            $query->where('creator_id', $employerId);
        }

        if (!empty($openPositionsOnly)) {
            $time = time();
            $expirationDaysSetting = getJobsGeneralSettings("jobs_expiration");

            $query->where(function (Builder $query) use ($time, $expirationDaysSetting) {
                $query->where(function (Builder $query) use ($time) {
                    $query->whereNotNull("hiring_end_date");
                    $query->where('hiring_end_date', ">", $time);
                });

                if (!empty($expirationDaysSetting)) {
                    $query->orWhere(function (Builder $query) use ($time, $expirationDaysSetting) {
                        $query->whereNull("hiring_end_date");
                        $query->whereRaw("(({$expirationDaysSetting} * 86400) + jobs.created_at) > {$time}");
                    });
                }
            });
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'highest_salary':
                    $query->orderByRaw("
                        CASE
                            WHEN payment_type = 'fixed_amount' THEN fixed_salary
                            WHEN payment_type = 'range' THEN max_salary
                            ELSE 0
                        END DESC
                    ");
                    break;
                case 'lowest_salary':
                    $query->orderByRaw("
                        CASE
                            WHEN payment_type = 'fixed_amount' THEN fixed_salary
                            WHEN payment_type = 'range' THEN min_salary
                            ELSE 0
                        END ASC
                    ");
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getListData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;
        $count = 9;

        $total = $query->get()->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $jobs = $query
            ->with([
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified', 'cover_img', 'profile_secondary_image');
                }
            ])
            ->get();

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
        $html = (string)view()->make('design_1.web.jobs.components.cards.grids.index', [
            'jobs' => $jobs,
            'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-24",
            'withoutStyles' => true
        ]);

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $jobs, $total, $count, true)
        ]);
    }

    public function show(Request $request, $slug)
    {
        $user = auth()->user();

        $contentLimitation = $this->checkContentLimitation($user);
        if ($contentLimitation != "ok") {
            return $contentLimitation;
        }


        $job = Job::query()
            ->where('slug', $slug)
            ->where('status', 'publish')
            ->with([
                'employmentType',
                'experienceLevel',
                'specificLocation',
                'tags',
                'faqs' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'prerequisites' => function ($query) {
                    $query->with([
                        'course' => function ($query) {
                            $query->with([
                                'teacher' => function ($qu) {
                                    $qu->select('id', 'username', 'full_name', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                                }
                            ]);
                        }
                    ]);

                    $query->orderBy('order', 'asc');
                },
                'relatedCourses' => function ($query) {
                    $query->whereHas('course', function ($query) {
                        $query->where('status', 'active');
                    });
                },
                'extraDescriptions' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified', 'cover_img', 'profile_secondary_image');
                },
            ])
            ->withCount([
                'jobRequests'
            ])
            ->first();

        if (!empty($job)) {
            $similarJobs = Job::query()->where('category_id', $job->category_id)
                ->where('id', '!=', $job->id)
                ->where('status', 'publish')
                ->with([
                    'creator' => function ($query) {
                        $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'bio', 'about', 'verified', 'cover_img', 'profile_secondary_image');
                    }
                ])
                ->get();

            $userRequestedToThisJob = false;
            $isFavorite = false;

            if (!empty($user)) {
                $isFavorite = Favorite::where('job_id', $job->id)
                    ->where('user_id', $user->id)
                    ->first();

                $userRequestedToThisJob = JobRequest::query()->where('job_id', $job->id)
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            $advertisingBanners = AdvertisingBanner::where('published', true)
                ->whereIn('position', ['job', 'job_sidebar'])
                ->get();

            $commentController = new CommentController();
            $jobComments = $commentController->getComments($request, 'job', $job->id);

            // Visit Logs
            $visitLogMixin = new VisitLogMixin();
            $visitLogMixin->storeVisit($request, $job->creator_id, $job->id, MorphTypesEnum::JOB);

            $pageRobot = getPageRobot('job_show'); // index

            $data = [
                'pageTitle' => $job->title,
                'pageDescription' => $job->seo_description,
                'pageRobot' => $pageRobot,
                'job' => $job,
                'advertisingBanners' => $advertisingBanners->where('position', 'job'),
                'advertisingBannersSidebar' => $advertisingBanners->where('position', 'job_sidebar'),
                'jobComments' => $jobComments,
                'similarJobs' => $similarJobs,
                'isFavorite' => $isFavorite,
                'userRequestedToThisJob' => $userRequestedToThisJob,
            ];

            return view('design_1.web.jobs.show.index', $data);
        }

        abort(404);
    }


    public function favorite(Request $request, $slug)
    {
        $user = auth()->user();
        $job = Job::query()
            ->where('slug', $slug)
            ->where('status', 'publish')
            ->first();

        if (!empty($user) and !empty($job)) {
            $isFavorite = Favorite::query()->where('job_id', $job->id)
                ->where('user_id', $user->id)
                ->first();

            if (empty($isFavorite)) {
                Favorite::create([
                    'user_id' => $user->id,
                    'job_id' => $job->id,
                    'created_at' => time()
                ]);
            } else {
                $isFavorite->delete();
            }

            return response()->json([
                'code' => 200,
            ]);
        }

        abort(422);
    }

    public function report(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'reason' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $job = Job::query()
            ->where('id', $id)
            ->where('status', 'publish')
            ->first();

        if (!empty($user) and !empty($job)) {

            JobReport::query()->create([
                'user_id' => $user->id,
                'job_id' => $job->id,
                'reason' => $data['reason'],
                'message' => $data['message'],
                'created_at' => time()
            ]);

            $notifyOptions = [
                '[u.name]' => $user->full_name,
                '[content_type]' => trans('update.upcoming_course')
            ];
            sendNotification("new_report_item_for_admin", $notifyOptions, 1);

            return response()->json([
                'code' => 200
            ], 200);
        }

        abort(422);
    }

    public function getShareModal($slug)
    {
        $job = Job::query()->where('slug', $slug)
            ->where('status', 'publish')
            ->first();

        if (!empty($job)) {
            $data = [
                'job' => $job
            ];

            $html = (string)view("design_1.web.jobs.show.includes.modals.share_modal", $data)->render();

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        }

        return response()->json([], 422);
    }

    public function getReportModal($slug)
    {
        $job = Job::query()->where('slug', $slug)
            ->where('status', 'publish')
            ->first();

        if (!empty($job)) {
            $data = [
                'job' => $job
            ];

            $html = (string)view("design_1.web.jobs.show.includes.modals.report_modal", $data)->render();

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        }

        return response()->json([], 422);
    }

}
