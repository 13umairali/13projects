<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Authentication\Models\Institute;
use Modules\SystemConfiguration\Models\Setting;

class SyncAllSettingsCommand extends Command
{
    protected $signature = 'settings:sync-all';
    // php artisan settings:sync-all

    protected $description = 'Sync all setting keys for each institute if missing';

    protected $defaultKeys = [
        'institute_name',
        'site_title',
        'phone',
        'email',
        'language',
        'google_map',
        'address',
        'on_google_map',
        'institute_code',
        'currency_symbol',
        'logo',
        'mail_type',
        'disabled_website',
        'copyright_text',
        'facebook_link',
        'google_plus_link',
        'youtube_link',
        'whats_app_link',
        'twitter_link',
        'eiin_code',
        'sms_gateway',
        'bulk_sms_api_key',
        'bulk_sms_sender_id',
        'twilio_sid',
        'twilio_token',
        'twilio_from_number',
        'header_notice',
        'app_version',
        'app_url',
        'tagline',
        'favicon',
        'theme_color',
        'background_image',
        'tax_type',
        'tax_percentage',
        'service_charge',
        'default_currency',
        'billing_prefix',
        'invoice_footer',
        'enable_kitchen_print',
        'enable_customer_copy',
        'enable_online_order',
        'delivery_charge',
        'minimum_order_amount',
        'auto_accept_order',
        'estimated_preparation_time',
        'slack_webhook_url',
        'telegram_bot_token',
        'telegram_chat_id',
        'twilio_sms_enabled',
        'email_notifications',
        'whatsapp_notifications',
        'auto_backup',
        'report_timezone',
        'data_retention_days',
        'sidebar_collapsed',
        'dark_mode',
        'default_dashboard',
        'bkash',
        'paystack',
        'razor_pay',
        'stripe',
        'ssl_commerz',
        'pvit',
        'paypal',
        'paymob_accept',
        'flutterwave',
        'senang_pay',
        'paytm',
        'cash_on_delivery',
        'max_table_capacity',
        'default_shift_start',
        'default_shift_end',
        'auto_logout_idle_minutes',
        'primary_color',
        'secondary_color',
        'primary_container_color',
        'dark_primary_color',
        'dark_secondary_color',
        'dark_container_color',
        'text_color',
        'dark_text_color',
        'sidebar_selected_bg_color',
        'sidebar_selected_text_color',
        'is_online',
        'latitude',
        'longitude',
        'delivery_radius_km',
        'delivery_fee',
        'delivery_partner_count',
        'delivery_time_avg',
        'pickup_enabled',
        'opening_time',
        'closing_time',
        'auto_accept_orders',
        'pre_order_enabled',
        'max_order_capacity',
        'avg_rating',
        'review_count',
        'total_orders',
        'last_order_time',
        'last_active_time',
        'loyalty_points_enabled',
        'offers_enabled',
        'social_media_links',
        'settings',
        'uuid',
        'email_smtp_host',
        'email_smtp_port',
        'email_smtp_username',
        'email_smtp_password',
        'email_smtp_encryption',
        'twilio_api_key',
        'twilio_api_secret',
        'twilio_sender_id',
        'twilio_api_url',
        'twilio_is_default',
        'nexmo_api_key',
        'nexmo_api_secret',
        'nexmo_sender_id',
        'nexmo_api_url',
        'nexmo_is_default',
        'muthofun_api_key',
        'smsglobal_api_key',
        'smsglobal_api_secret',
        'smsglobal_sender_id',
        'smsglobal_api_url',
        'smsglobal_extra_key',
        'smsglobal_is_default',
    ];

    public function handle()
    {
        $institutes = Institute::all();
        foreach ($institutes as $institute) {
            foreach ($this->defaultKeys as $key) {
                $extraValue = [];
                $defaultValues = [
                    'institute_name' => $extraValue,
                ];

                $setting = Setting::where('institute_id', $institute->id)
                    ->where('key', $key)
                    ->first();
                if (! $setting) {
                    // Create if it doesn't exist
                    Setting::create([
                        'institute_id' => $institute->id,
                        'key' => $key,
                        'value' => $defaultValues[$key] ?? null,
                        'type' => 'vendor',
                        'is_active' => true,
                    ]);

                    $this->info("Added missing setting '{$key}' for Institute ID: {$institute->id}");
                } elseif (is_null($setting->value)) {
                    // Update only if value is NULL
                    if (array_key_exists($key, $defaultValues)) {
                        $setting->update(['value' => $defaultValues[$key]]);
                        $this->info("Updated '{$key}' for Institute ID: {$institute->id} because value was null");
                    }
                } else {
                    // Skip if already exists with a non-null value
                    $this->line("Setting '{$key}' already exists for Institute ID: {$institute->id} with non-null value — skipped.");
                }
            }
        }

        $this->info('All settings sync complete.');
    }
}
