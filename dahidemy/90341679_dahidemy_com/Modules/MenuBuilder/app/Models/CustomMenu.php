<?php

namespace Modules\MenuBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class CustomMenu extends Model
{
    protected $table = 'custom_menus';

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 2;

    /**
     * Safe, fixed list of routes an admin can point a menu item at. Deliberately
     * an allowlist rather than letting an admin type any route name — a typo'd
     * or renamed route would otherwise throw a RouteNotFoundException on every
     * page load (the exact bug that took the whole dashboard down earlier in
     * this project). Each entry degrades to '#' via resolveUrl() if the route
     * genuinely doesn't exist (e.g. that module isn't installed).
     */
    public const ROUTE_OPTIONS = [
        'admission-form-page' => 'Admission Form',
        'result-page' => 'Exam Results',
        'academic-images' => 'Gallery',
        'contact' => 'Contact Page',
        'eventenrollment.index' => 'Events',
        'public-form.index' => 'Forms',
        'privacy-policy' => 'Privacy Policy',
        'terms-conditions' => 'Terms & Conditions',
        'refund-policy' => 'Refund Policy',
        'cookies-policy' => 'Cookies Policy',
    ];

    public const ANCHOR_OPTIONS = [
        'about' => 'About Section (Homepage)',
        'courses' => 'Courses Section (Homepage)',
        'events' => 'Events Section (Homepage)',
        'testimonials' => 'Testimonials Section (Homepage)',
        'faq' => 'FAQ Section (Homepage)',
        'contact' => 'Contact Section (Footer)',
    ];

    protected $fillable = [
        'institute_id',
        'branch_id',
        'parent_id',
        'label',
        'link_type',
        'link_value',
        'open_new_tab',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'open_new_tab' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('sort_order');
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Resolve this item's href. Never throws — a bad/stale route or missing
     * module just quietly resolves to '#' rather than breaking the page.
     */
    public function resolveUrl(): string
    {
        return match ($this->link_type) {
            'home' => url('/'),
            'anchor' => url('/').'/#'.ltrim((string) $this->link_value, '#'),
            'route' => (isset(self::ROUTE_OPTIONS[$this->link_value]) && Route::has($this->link_value))
                ? route($this->link_value)
                : '#',
            'custom_url' => $this->link_value ?: '#',
            'academics_pages' => '#', // rendered specially in the view, not linked directly
            default => '#',
        };
    }
}
