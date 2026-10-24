<?php

namespace Modules\HomepageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $table = 'homepage_sections';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'section_key',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Every section an admin can toggle/reorder, in the default order a
     * brand-new institute gets before they've customized anything. The
     * "hero" banner itself isn't listed here — it's always the first thing
     * on the page and isn't optional.
     *
     * Each entry's 'view' is the fully-qualified Blade view name, resolved
     * with @includeIf so a missing module (e.g. Blog not installed) just
     * quietly skips that section instead of erroring.
     */
    public const REGISTRY = [
        'about' => ['label' => 'About Us', 'view' => 'themes.theme-three.sections.about'],
        'director-message' => ['label' => "Director's Message", 'view' => 'homepagebuilder::sections.director-message'],
        'stats' => ['label' => 'Achievements / Stats', 'view' => 'themes.theme-three.sections.stats'],
        'cta-admission' => ['label' => 'Admission CTA Banner', 'view' => 'themes.theme-three.sections.cta-admission'],
        'dynamic-forms' => ['label' => 'Featured Forms', 'view' => 'themes.theme-three.sections.dynamic-forms'],
        'courses' => ['label' => 'Courses', 'view' => 'themes.theme-three.sections.courses'],
        'notices' => ['label' => 'Notices / Announcements', 'view' => 'themes.theme-three.sections.notices'],
        'gallery' => ['label' => 'Campus Gallery', 'view' => 'themes.theme-three.sections.gallery'],
        'teachers' => ['label' => 'Teachers', 'view' => 'themes.theme-three.sections.teachers'],
        'staffs' => ['label' => 'Staff', 'view' => 'themes.theme-three.sections.staffs'],
        'mobile-app' => ['label' => 'Mobile App Promo', 'view' => 'themes.theme-three.sections.mobile-app'],
        'events' => ['label' => 'Events', 'view' => 'themes.theme-three.sections.events'],
        'upcoming-exams' => ['label' => 'Upcoming Exams', 'view' => 'themes.theme-three.sections.upcoming-exams'],
        'result-announcements' => ['label' => 'Result Announcements', 'view' => 'themes.theme-three.sections.result-announcements'],
        'blog' => ['label' => 'Latest Blog Posts', 'view' => 'blog::sections.homepage-teaser'],
        'testimonials' => ['label' => 'Testimonials', 'view' => 'themes.theme-three.sections.testimonials'],
        'faq' => ['label' => 'FAQ', 'view' => 'themes.theme-three.sections.faq'],
    ];

    /**
     * Every section, in this institute's chosen order, seeding sensible
     * defaults the first time this is ever called for a given institute so
     * a brand-new site isn't blank until an admin visits the settings page.
     */
    public static function forInstitute(int $instituteId, ?int $branchId): \Illuminate\Support\Collection
    {
        $existing = self::where('institute_id', $instituteId)->orderBy('sort_order')->get();

        if ($existing->isEmpty()) {
            $order = 0;
            foreach (array_keys(self::REGISTRY) as $key) {
                self::create([
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'section_key' => $key,
                    'is_enabled' => true,
                    'sort_order' => $order++,
                ]);
            }

            return self::where('institute_id', $instituteId)->orderBy('sort_order')->get();
        }

        // If new section types were added to REGISTRY after this institute
        // already had rows, append them at the end rather than losing them
        // silently — keeps upgrades additive instead of hiding new features.
        $missingKeys = array_diff(array_keys(self::REGISTRY), $existing->pluck('section_key')->all());
        if (! empty($missingKeys)) {
            $order = $existing->max('sort_order') + 1;
            foreach ($missingKeys as $key) {
                self::create([
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'section_key' => $key,
                    'is_enabled' => true,
                    'sort_order' => $order++,
                ]);
            }

            return self::where('institute_id', $instituteId)->orderBy('sort_order')->get();
        }

        return $existing;
    }

    /**
     * The enabled sections' view paths, in order — exactly what the
     * homepage template needs, with unknown/removed keys safely skipped.
     */
    public static function enabledViewsForInstitute(int $instituteId, ?int $branchId): array
    {
        return self::forInstitute($instituteId, $branchId)
            ->where('is_enabled', true)
            ->map(fn ($row) => self::REGISTRY[$row->section_key]['view'] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
