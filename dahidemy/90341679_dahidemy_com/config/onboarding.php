<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Approver identity
    |--------------------------------------------------------------------------
    |
    | Public onboarding is unauthenticated, so there is no acting user to record
    | against an automatic approval. Set ONBOARDING_APPROVER_ID to the super
    | admin's user id to attribute these approvals explicitly.
    |
    | When left null we fall back to the lowest-id user, which on a standard
    | install is the super admin created by the seeder. The column is nullable,
    | so if no user exists the approval is still recorded with a null approver
    | rather than failing the sign-up.
    |
    */

    'approver_id' => env('ONBOARDING_APPROVER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Provisioning defaults
    |--------------------------------------------------------------------------
    */

    'default_branch_name' => env('ONBOARDING_DEFAULT_BRANCH_NAME', 'Main Branch'),

    'admin_role' => env('ONBOARDING_ADMIN_ROLE', 'Admin'),

    /*
    |--------------------------------------------------------------------------
    | Trial plan
    |--------------------------------------------------------------------------
    |
    | The trial plan is resolved by its `is_free` flag rather than by a literal
    | id, so reordering or reseeding the plans table cannot silently hand new
    | sign-ups the wrong package.
    |
    | `fallback_duration_days` only applies when the resolved plan has no
    | duration set. Without it, `addDays(null)` yields an end date of "now" and
    | the subscription expires the instant it is created.
    |
    */

    'trial' => [
        'fallback_duration_days' => (int) env('ONBOARDING_TRIAL_FALLBACK_DAYS', 14),
    ],

    /*
    |--------------------------------------------------------------------------
    | Initial home sections
    |--------------------------------------------------------------------------
    |
    | Seeded for every new institute so the tenant's public site is not empty on
    | first load. These were previously hardcoded in the public sign-up flow only,
    | which meant institutes created through admin approval never got them.
    |
    | Serial is derived from array order.
    |
    */

    'home_sections' => [
        ['title' => 'About Us', 'description' => 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.'],
        ['title' => 'Why Choose Us', 'description' => 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.'],
        ['title' => 'Admission', 'description' => 'Start your journey with our easy registration and onboarding process designed for candidates and employers.'],
        ['title' => 'Teachers', 'description' => 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.'],
        ['title' => 'Feedback', 'description' => 'Read success stories and testimonials from job seekers and employers using our platform.'],
        ['title' => 'Mobile App', 'description' => 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.'],
        ['title' => 'Events', 'description' => 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.'],
        ['title' => 'FAQ', 'description' => 'Find answers to the most common questions about our platform, applications, subscriptions, and services.'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Initial institute settings
    |--------------------------------------------------------------------------
    |
    | Seeded verbatim for every new institute, except for the keys listed under
    | 'derived_from_institute' below, which are populated from the institute's
    | own record at provisioning time.
    |
    */

    'institute_settings' => [

        'type' => 'general',

        /*
         * Populated from the institute record rather than from the defaults
         * below. Previously every new school was seeded with the literal
         * "Demo Collage" / "Demo Title" and blank contact details, even though
         * the real values were supplied on the sign-up form.
         */
        'derived_from_institute' => [
            'school_name' => 'name',
            'site_title' => 'name',
            'email' => 'email',
            'phone' => 'phone',
            'address' => 'address',
            'logo' => 'logo',
        ],

        'defaults' => [
            'school_name' => '',
            'site_title' => '',
            'phone' => '',
            'email' => '',
            'language' => env('INSTITUTE_DEFAULT_LANGUAGE', 'en'),
            'google_map' => '',
            'address' => '',
            'on_google_map' => '',
            'institute_code' => '',
            'timezone' => env('INSTITUTE_DEFAULT_TIMEZONE', 'Asia/Dhaka'),
            'academic_year' => '1',
            'currency_symbol' => env('INSTITUTE_DEFAULT_CURRENCY_SYMBOL', '$'),
            'mail_type' => 'mail',
            'logo' => 'logo.png',
            'disabled_website' => 'no',
            'copyright_text' => env('INSTITUTE_DEFAULT_COPYRIGHT_TEXT', ''),
            'exam_result_phone' => '',
            'tuition_fee_phone' => '',
            'facebook_link' => 'https://www.facebook.com/',
            'google_plus_link' => 'https://www.google.com/',
            'youtube_link' => 'https://www.youtube.com/',
            'whats_app_link' => '',
            'twitter_link' => 'https://www.twitter.com',
            'eiin_code' => '',
            'sms_gateway' => env('INSTITUTE_DEFAULT_SMS_GATEWAY', 'twilio'),
            'bulk_sms_api_key' => '',
            'bulk_sms_sender_id' => '',
            'twilio_sid' => '',
            'twilio_token' => '',
            'twilio_from_number' => '',
            'zoom_account_id' => '',
            'zoom_client_key' => '',
            'zoom_client_secret' => '',
            'header_notice' => '',
            'exam_result_status' => 'no',
            'admission_display_status' => 'no',
            'tc_amount' => 0,
            'app_version' => '1.0.0',
            'app_url' => '',

            /*
             * Public website design. Institutes can change these from
             * Settings > Website Design; these are just the starting point so
             * a freshly registered site isn't left on whatever the last
             * institute happened to pick.
             */
            'website_theme' => 'theme-one',
            'website_primary_color' => '#7c3aed',
            'website_secondary_color' => '#0f172a',
            'website_accent_color' => '#ec4899',
        ],
    ],
];
