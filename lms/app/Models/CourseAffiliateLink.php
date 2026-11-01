<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAffiliateLink extends Model
{
    protected $table = 'course_affiliate_links';
    public $timestamps = false;
    protected $guarded = ['id'];

    const TYPE_COURSE = 'course';
    const TYPE_BUNDLE = 'bundle';
    const TYPE_PRODUCT = 'product';
    const TYPE_EVENT = 'event';

    static $types = [
        self::TYPE_COURSE,
        self::TYPE_BUNDLE,
        self::TYPE_PRODUCT,
        self::TYPE_EVENT,
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    /**
     * Get the related item model based on type
     */
    public function getItem()
    {
        switch ($this->type) {
            case self::TYPE_BUNDLE:
                return Bundle::find($this->item_id);
            case self::TYPE_PRODUCT:
                return Product::find($this->item_id);
            case self::TYPE_EVENT:
                return Event::find($this->item_id);
            case self::TYPE_COURSE:
            default:
                return Webinar::find($this->item_id);
        }
    }

    /**
     * Generate affiliate URL based on type
     */
    public function getAffiliateUrl($affiliateCode)
    {
        $item = $this->getItem();
        if (!$item)
            return '#';

        switch ($this->type) {
            case self::TYPE_BUNDLE:
                return url('/bundle-aff/' . $affiliateCode . '/' . $item->slug);
            case self::TYPE_PRODUCT:
                return url('/product-aff/' . $affiliateCode . '/' . $item->slug);
            case self::TYPE_EVENT:
                return url('/event-aff/' . $affiliateCode . '/' . $item->slug);
            case self::TYPE_COURSE:
            default:
                return url('/course-aff/' . $affiliateCode . '/' . $item->slug);
        }
    }

    public static function getTypeLabel($type)
    {
        $labels = [
            self::TYPE_COURSE => trans('webinars.course'),
            self::TYPE_BUNDLE => trans('update.bundle'),
            self::TYPE_PRODUCT => trans('update.product'),
            self::TYPE_EVENT => trans('update.event'),
        ];

        return $labels[$type] ?? $type;
    }
}
