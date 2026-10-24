<?php

namespace App\Support;

use App\Models\Central\Plan;

/**
 * Which role permissions each plan FEATURE unlocks.
 *
 * A tenant's plan (assigned by the network admin) lists feature keys — see
 * Plan::AVAILABLE_FEATURES. Routes for those features are already gated by
 * the `tenant.feature:*` middleware; this map is the same contract applied to
 * ROLE BUILDING, so a business admin composing roles for their staff only ever
 * sees (and can only save) permissions their plan actually includes.
 *
 * Permissions that appear under no feature are "core" and always available
 * (products, sales, purchases, customers, settings, most reports…).
 *
 * Keep this in step with the permission cards in
 * resources/src/views/app/pages/settings/permissions/*_permission.vue.
 */
class PlanPermissionMap
{
    public const MAP = [
        'pos' => ['Pos_view', 'pos_settings', 'kitchen_display_view', 'kitchen_display_manage', 'customer_display_screen_setup', 'cash_register_report'],
        'online_orders' => ['Store_settings_view', 'Orders_view', 'Collections_view', 'Banners_view', 'Subscribers_view', 'Messages_view'],
        'hrm' => ['view_employee', 'add_employee', 'edit_employee', 'delete_employee', 'company', 'department', 'designation', 'office_shift', 'attendance', 'leave', 'holiday', 'payroll', 'report_attendance_summary'],
        'accounting' => ['accounting_dashboard', 'chart_of_accounts', 'journal_entries', 'trial_balance', 'accounting_profit_loss', 'balance_sheet', 'accounting_tax_report'],
        'woocommerce' => ['woocommerce_settings'],
        'shopify' => ['shopify_settings', 'shopify_stores', 'shopify_sync', 'shopify_logs'],
        'transfers' => ['transfer_view', 'transfer_add', 'transfer_edit', 'transfer_delete', 'Stock_Transfer_Report'],
        'service_maintenance' => ['service_jobs', 'service_jobs_report', 'checklist_completion_report', 'customer_maintenance_history_report'],
        'ai_reports' => ['AI_Reports'],
        'promotions' => ['promotion'],
        'contracts' => ['contracts'],
        'projects' => ['projects', 'tasks', 'project_milestones', 'project_timesheets', 'project_reports'],
        'bookings' => ['bookings', 'trays'],
        'assets' => ['assets'],
        'quotations' => ['Quotations_view', 'Quotations_add', 'Quotations_edit', 'Quotations_delete', 'edit_product_quotation', 'edit_tax_discount_shipping_quotation'],
        'commissions' => ['commissions_view', 'commissions_add', 'commissions_edit', 'commissions_delete'],
        'recruitment' => ['recruit_job', 'recruit_category', 'recruit_candidate', 'recruit_application', 'recruit_interview'],
        'meetings' => ['meeting', 'meeting_attendance', 'meeting_report'],
        'marketing' => ['marketing_dashboard', 'marketing_campaigns', 'marketing_segments', 'marketing_templates', 'marketing_reports', 'marketing_settings'],
        'knowledge_base' => ['knowledge_base_view'],
        'quickbooks' => ['quickbooks_settings'],
        'zapier' => ['zapier_settings'],
        'slack' => ['slack_settings'],
        'telegram' => ['telegram_settings'],
        'salla' => ['salla_settings'],
        'xero' => ['xero_settings'],
        'prestashop' => ['prestashop_settings'],
        'google_sheets' => ['google_sheets_settings'],
        'mailchimp' => ['mailchimp_settings'],
        'jumia' => ['jumia_settings'],
        'protocol' => ['protocol_view', 'protocol_add', 'protocol_act', 'protocol_manage'],
        'zatca' => ['zatca_settings'],
        'fbr' => ['fbr_settings'],
        'webhooks' => ['webhooks_view', 'webhooks_add', 'webhooks_edit', 'webhooks_delete'],
        'documents' => ['documents_view', 'documents_add', 'documents_edit', 'documents_delete'],
        'manufacturing' => ['mrp_boms', 'mrp_production', 'mrp_quality', 'mrp_planning', 'mrp_reports'],
        'fleet' => ['fleet_vehicles_view', 'fleet_vehicles_add', 'fleet_vehicles_edit', 'fleet_vehicles_delete', 'fleet_maintenance', 'fleet_fuel', 'fleet_assignments', 'fleet_reports'],
        'hospital' => ['hms_dashboard', 'hms_patients_view', 'hms_patients_add', 'hms_patients_edit', 'hms_patients_delete', 'hms_doctors', 'hms_departments', 'hms_appointments', 'hms_visits', 'hms_admissions', 'hms_wards', 'hms_lab', 'hms_billing', 'hms_reports'],
        'school' => ['school_dashboard', 'school_students_view', 'school_students_add', 'school_students_edit', 'school_students_delete', 'school_teachers', 'school_academics', 'school_enrollment', 'school_attendance', 'school_exams', 'school_timetable', 'school_fees', 'school_reports'],
    ];

    /** permission slug => feature key, for every mapped slug. */
    public static function featureOf(): array
    {
        static $index = null;
        if ($index === null) {
            $index = [];
            foreach (self::MAP as $feature => $slugs) {
                foreach ($slugs as $slug) {
                    $index[$slug] = $feature;
                }
            }
        }

        return $index;
    }

    /**
     * Slugs the given plan does NOT unlock. With no plan at all every mapped
     * slug is locked — a tenant without a subscription gets the core set only.
     */
    public static function lockedFor(?Plan $plan): array
    {
        $locked = [];
        foreach (self::MAP as $feature => $slugs) {
            if (! $plan || ! $plan->hasFeature($feature)) {
                array_push($locked, ...$slugs);
            }
        }

        return $locked;
    }

    /** The subset of $slugs the plan does not allow, as slug => feature label. */
    public static function disallowed(?Plan $plan, array $slugs): array
    {
        $locked = array_flip(self::lockedFor($plan));
        $index = self::featureOf();
        $out = [];
        foreach ($slugs as $slug) {
            if (isset($locked[$slug])) {
                $feature = $index[$slug];
                $out[$slug] = Plan::AVAILABLE_FEATURES[$feature]['label'] ?? $feature;
            }
        }

        return $out;
    }
}
