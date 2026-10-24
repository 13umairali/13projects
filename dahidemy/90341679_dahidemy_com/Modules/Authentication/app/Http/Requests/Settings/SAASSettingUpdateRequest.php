<?php

namespace Modules\Authentication\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class SAASSettingUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'site_name' => 'nullable|string|max:255',
            'site_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'office_phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'google_map' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'on_google_map' => 'nullable|string|max:255',
            'currency_symbol' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'disabled_website' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:255',
            'facebook_link' => 'nullable|string|max:255',
            'google_plus_link' => 'nullable|string|max:255',
            'youtube_link' => 'nullable|string|max:255',
            'whats_app_link' => 'nullable|string|max:255',
            'twitter_link' => 'nullable|string|max:255',
            'header_notice' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:255',
            'app_url' => 'nullable|string|max:255',
            'play_store_link' => 'nullable|string|max:255',
            'app_store_link' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:255',
            'secondary_color' => 'nullable|string|max:255',
            'primary_container_color' => 'nullable|string|max:255',
            'dark_primary_color' => 'nullable|string|max:255',
            'dark_secondary_color' => 'nullable|string|max:255',
            'dark_container_color' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:255',
            // The seeded row is misspelled `dark_text_colo`; accept both so the
            // existing row can be written and the correct one kept in step.
            'dark_text_colo' => 'nullable|string|max:255',
            'dark_text_color' => 'nullable|string|max:255',
            'sidebar_selected_bg_color' => 'nullable|string|max:255',
            'sidebar_selected_text_color' => 'nullable|string|max:255',
            'bkash' => 'nullable|string',
            'paystack' => 'nullable|string',
            'razor_pay' => 'nullable|string',
            'stripe' => 'nullable|string',
            'ssl_commerz' => 'nullable|string',
            'pvit' => 'nullable|string',
            'paypal' => 'nullable|string',
            'paymob_accept' => 'nullable|string',
            'flutterwave' => 'nullable|string',
            'senang_pay' => 'nullable|string',
            'paytm' => 'nullable|string',
            'vercel_project_id' => 'nullable|string|max:255',
            'vercel_token' => 'nullable|string|max:255',
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
