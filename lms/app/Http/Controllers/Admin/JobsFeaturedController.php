<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Setting;
use Illuminate\Http\Request;

class JobsFeaturedController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('admin_jobs_featured');
        removeContentLocale();

        $jobs = Job::query()->where('status', 'publish')
            ->get();

        $setting = Setting::where('page', 'general')
            ->where('name', Setting::$jobsFeaturedSettingsName)
            ->first();

        $settingValues = !empty($setting) ? $setting->value : null;

        if (!empty($settingValues)) {
            $settingValues = json_decode($settingValues, true);
        }


        $data = [
            'pageTitle' => trans('update.featured_jobs'),
            'jobs' => $jobs,
            'settingValues' => $settingValues,
        ];

        return view('admin.jobs.featured.index', $data);
    }

}
