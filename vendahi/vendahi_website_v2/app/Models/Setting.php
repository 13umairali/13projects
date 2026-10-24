<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'currency_id', 'email', 'CompanyName', 'CompanyPhone', 'CompanyAdress', 'quotation_with_stock',
        'logo', 'footer', 'developed_by', 'client_id', 'warehouse_id', 'default_account_id', 'default_payment_method_id', 'default_language', 'show_language',
        'is_invoice_footer', 'invoice_footer', 'invoice_format', 'invoice_logo_width', 'invoice_logo_height', 'app_name', 'favicon', 'page_title_suffix', 'customize_button_visible', 'hide_site_name', 'point_to_amount_rate',
        'sidebar_show_logo', 'sidebar_logo_width', 'sidebar_logo_height',
        'vat_number', 'company_name_ar', 'zatca_enabled', 'default_tax', 'default_dashboard_date_range', 'dashboard_section_order', 'dashboard_grid_layout', 'dashboard_font_size', 'dashboard_font_family', 'date_format',
        'sale_return_prefix', 'purchase_return_prefix',
        'price_format', 'dark_mode', 'rtl', 'sms_gateway',
        // Login page appearance
        'login_hero_title', 'login_hero_subtitle', 'login_panel_title', 'login_panel_subtitle',
        'login_hero_badge', 'login_hero_feature_1', 'login_hero_feature_2', 'login_hero_feature_3',
        'login_btn_text', 'login_footer_text',
        // Cloud backup settings
        'backup_cloud_enabled', 'backup_keep_local', 'backup_cloud_provider', 'backup_cloud_path',
        'backup_s3_bucket', 'backup_s3_region', 'backup_s3_access_key', 'backup_s3_secret_key',
        'backup_s3_endpoint', 'backup_s3_path_style',
        'backup_gdrive_folder_id', 'backup_gdrive_access_token', 'backup_gdrive_refresh_token',
        'backup_gdrive_client_id', 'backup_gdrive_client_secret',
        'backup_dropbox_path', 'backup_dropbox_access_token',
        'google_calendar_refresh_token', 'google_calendar_calendar_id', 'timezone',
        'google_calendar_client_id', 'google_calendar_client_secret', 'google_calendar_redirect_uri',
        'offline_sync_enabled', 'enable_3_decimal_pricing', 'show_product_gtin', 'show_serial_tracking',
        'enable_kitchen_display', 'enable_multi_pack_selling', 'allow_overselling', 'enable_multi_currency',
        'accounting_auto_generate_journals',
        // Settings → Modules: JSON map of switched-off business modules
        'module_flags',
        'purchase_extra_charges_enabled', 'purchase_custom_fields_enabled',
        // PWA manifest overrides (empty = fall back to app_name / built-in default)
        'pwa_enabled', 'pwa_name', 'pwa_short_name', 'pwa_description', 'pwa_start_url',
        'pwa_display', 'pwa_orientation', 'pwa_theme_color', 'pwa_background_color',
    ];

    protected $casts = [
        'currency_id' => 'integer',
        'client_id' => 'integer',
        'quotation_with_stock' => 'integer',
        'show_language' => 'integer',
        'is_invoice_footer' => 'integer',
        'invoice_logo_width' => 'integer',
        'invoice_logo_height' => 'integer',
        'warehouse_id' => 'integer',
        'default_account_id' => 'integer',
        'default_payment_method_id' => 'integer',
        'point_to_amount_rate' => 'double',
        'zatca_enabled' => 'boolean',
        'customize_button_visible' => 'boolean',
        'hide_site_name' => 'boolean',
        'sidebar_show_logo' => 'boolean',
        'sidebar_logo_width' => 'integer',
        'sidebar_logo_height' => 'integer',
        'default_tax' => 'double',
        'backup_cloud_enabled' => 'boolean',
        'backup_keep_local' => 'boolean',
        'backup_s3_path_style' => 'boolean',
        'offline_sync_enabled' => 'boolean',
        'enable_3_decimal_pricing' => 'boolean',
        'show_product_gtin' => 'boolean',
        'show_serial_tracking' => 'boolean',
        'enable_kitchen_display' => 'boolean',
        'enable_multi_pack_selling' => 'boolean',
        'allow_overselling' => 'boolean',
        'enable_multi_currency' => 'boolean',
        'accounting_auto_generate_journals' => 'boolean',
        'purchase_extra_charges_enabled' => 'boolean',
        'purchase_custom_fields_enabled' => 'boolean',
        'google_calendar_client_secret' => 'encrypted',
        'google_calendar_refresh_token' => 'encrypted',
    ];

    /**
     * Curated Google Font families tenants may pick for the dashboard UI.
     * Keyed by the exact Google family name (also used as the CSS font-family).
     * Every family supports weights 400/500/600/700 so the css2 request never
     * errors. `arabic` groups the settings dropdown; stored values NOT in this
     * list are treated as raw CSS stacks (legacy system-font values).
     */
    public const DASHBOARD_GOOGLE_FONTS = [
        // Arabic-first families
        'Cairo'                => ['label' => 'Cairo (القاهرة)', 'arabic' => true],
        'Tajawal'              => ['label' => 'Tajawal (تجوال)', 'arabic' => true],
        'Almarai'              => ['label' => 'Almarai (المراعي)', 'arabic' => true],
        'IBM Plex Sans Arabic' => ['label' => 'IBM Plex Sans Arabic', 'arabic' => true],
        'Noto Sans Arabic'     => ['label' => 'Noto Sans Arabic', 'arabic' => true],
        'Noto Kufi Arabic'     => ['label' => 'Noto Kufi Arabic', 'arabic' => true],
        'Readex Pro'           => ['label' => 'Readex Pro', 'arabic' => true],
        'Changa'               => ['label' => 'Changa (تشانغا)', 'arabic' => true],
        'El Messiri'           => ['label' => 'El Messiri (المسيري)', 'arabic' => true],
        'Amiri'                => ['label' => 'Amiri (أميري — نسخ)', 'arabic' => true],
        'Rubik'                => ['label' => 'Rubik (Arabic + Latin)', 'arabic' => true],
        // Latin families
        'Inter'             => ['label' => 'Inter', 'arabic' => false],
        'Open Sans'         => ['label' => 'Open Sans', 'arabic' => false],
        'Poppins'           => ['label' => 'Poppins', 'arabic' => false],
        'Montserrat'        => ['label' => 'Montserrat', 'arabic' => false],
        'Nunito'            => ['label' => 'Nunito', 'arabic' => false],
        'Work Sans'         => ['label' => 'Work Sans', 'arabic' => false],
        'DM Sans'           => ['label' => 'DM Sans', 'arabic' => false],
        'Plus Jakarta Sans' => ['label' => 'Plus Jakarta Sans', 'arabic' => false],
        'Figtree'           => ['label' => 'Figtree', 'arabic' => false],
        'Source Sans 3'     => ['label' => 'Source Sans 3', 'arabic' => false],
    ];

    /**
     * Legacy raw CSS stacks the old dropdown offered; still accepted so
     * tenants who saved one before the Google Fonts feature keep working.
     */
    public const DASHBOARD_SYSTEM_FONTS = [
        'inherit'                                 => 'Inherit',
        'Arial, sans-serif'                       => 'Arial',
        'Georgia, serif'                          => 'Georgia',
        '"Times New Roman", Times, serif'         => 'Times New Roman',
        'Verdana, Geneva, sans-serif'             => 'Verdana',
        'Tahoma, Geneva, sans-serif'              => 'Tahoma',
        '"Segoe UI", Tahoma, sans-serif'          => 'Segoe UI',
        'system-ui, -apple-system, sans-serif'    => 'System UI',
    ];

    /** Whether the stored dashboard_font_family is a curated Google family. */
    public static function isDashboardGoogleFont(?string $family): bool
    {
        return $family !== null && $family !== ''
            && array_key_exists($family, self::DASHBOARD_GOOGLE_FONTS);
    }

    /** Every value update_settings accepts for dashboard_font_family. */
    public static function dashboardFontKeys(): array
    {
        return array_merge(
            array_keys(self::DASHBOARD_GOOGLE_FONTS),
            array_keys(self::DASHBOARD_SYSTEM_FONTS)
        );
    }

    public function Currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }

    public function Client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}
