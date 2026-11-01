<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobsSettingsController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('admin_jobs_settings');

        removeContentLocale();

        $selectedLocale = mb_strtolower($request->get('locale', Setting::$defaultSettingsLocale));

        $settings = Setting::where('page', 'general')
            ->whereIn('name', [Setting::$jobsGeneralSettingsName, Setting::$jobsHeroContentSettingsName, Setting::$jobsApplicationFormSettingsName])
            ->get();

        $generalSettings = $settings->where('name', Setting::$jobsGeneralSettingsName)->first();
        $heroContentSettings = $settings->where('name', Setting::$jobsHeroContentSettingsName)->first();
        $applicationFormSettings = $settings->where('name', Setting::$jobsApplicationFormSettingsName)->first();

        $generalSettingValues = [];
        $heroContentSettingValues = [];
        $applicationFormSettingValues = [];

        if (!empty($generalSettings)) {
            $generalSettingValues = json_decode($generalSettings->value, true);
        }

        if (!empty($heroContentSettings)) {
            $heroContentValue = (!empty($heroContentSettings->translate($selectedLocale))) ? $heroContentSettings->translate($selectedLocale)->value : null;

            if (!empty($heroContentValue)) {
                $heroContentSettingValues = json_decode($heroContentValue, true);
            }
        }

        if (!empty($applicationFormSettings)) {
            $applicationFormValue = (!empty($applicationFormSettings->translate($selectedLocale))) ? $applicationFormSettings->translate($selectedLocale)->value : null;

            if (!empty($applicationFormValue)) {
                $applicationFormSettingValues = json_decode($applicationFormValue, true);
            }
        }

        $data = [
            'pageTitle' => trans('update.jobs_settings'),
            'generalSettingValues' => $generalSettingValues,
            'heroContentSettingValues' => $heroContentSettingValues,
            'applicationFormSettingValues' => $applicationFormSettingValues,
            'selectedLocale' => $selectedLocale,
        ];

        return view('admin.jobs.settings.index', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_jobs_settings');

        $data = $request->all();
        $page = $data['page'];
        $name = $data['name'];
        $locale = mb_strtolower($request->get('locale', Setting::$defaultSettingsLocale));
        $newValues = $data['value'];
        $values = [];

        $settings = Setting::where('name', $name)->first();

        if (!empty($settings)) {
            $heroContentValue = (!empty($settings->translate($locale))) ? $settings->translate($locale)->value : null;

            if (!empty($heroContentValue)) {
                $values = json_decode($heroContentValue, true);
            }
        }

        if (!empty($newValues) and !empty($values)) {
            foreach ($newValues as $newKey => $newValue) {
                foreach ($values as $key => $value) {
                    if ($key == $newKey) {
                        $values[$key] = $newValue;
                        unset($newValues[$key]);
                    }
                }
            }
        }

        if (!empty($newValues)) {
            $values = array_merge((array)$values, $newValues);
        }

        $settings = Setting::updateOrCreate(
            ['name' => $name],
            [
                'page' => $page,
                'updated_at' => time(),
            ]
        );

        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $settings->id,
                'locale' => mb_strtolower($locale)
            ],
            [
                'value' => json_encode($values),
            ]
        );

        cache()->forget('settings.' . $name);

        return back();
    }
}
