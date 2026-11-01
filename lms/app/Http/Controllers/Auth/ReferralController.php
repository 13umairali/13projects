<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCode;
use App\Models\Bundle;
use App\Models\Event;
use App\Models\Product;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ReferralController extends Controller
{
    public function referral($code)
    {
        $check = AffiliateCode::where('code', $code)->first();

        if (!empty($check)) {
            Cookie::queue('referral_code', $code, 24 * 60);
        }

        return redirect('/register');
    }

    public function courseReferral($code, $slug)
    {
        $affiliateCode = AffiliateCode::where('code', $code)->first();
        $webinar = Webinar::where('slug', $slug)
            ->where('status', Webinar::$active)
            ->first();

        if (!empty($affiliateCode) && !empty($webinar)) {
            session([
                'course_affiliate_code' => $code,
                'course_affiliate_type' => 'course',
                'course_affiliate_item_id' => $webinar->id,
                'course_affiliate_user_id' => $affiliateCode->user_id,
            ]);
        }

        $courseUrl = $webinar ? $webinar->getUrl() : '/classes';
        return redirect($courseUrl);
    }

    public function bundleReferral($code, $slug)
    {
        $affiliateCode = AffiliateCode::where('code', $code)->first();
        $bundle = Bundle::where('slug', $slug)
            ->where('status', Bundle::$active)
            ->first();

        if (!empty($affiliateCode) && !empty($bundle)) {
            session([
                'course_affiliate_code' => $code,
                'course_affiliate_type' => 'bundle',
                'course_affiliate_item_id' => $bundle->id,
                'course_affiliate_user_id' => $affiliateCode->user_id,
            ]);
        }

        $url = $bundle ? $bundle->getUrl() : '/classes';
        return redirect($url);
    }

    public function productReferral($code, $slug)
    {
        $affiliateCode = AffiliateCode::where('code', $code)->first();
        $product = Product::where('slug', $slug)
            ->where('status', Product::$active)
            ->first();

        if (!empty($affiliateCode) && !empty($product)) {
            session([
                'course_affiliate_code' => $code,
                'course_affiliate_type' => 'product',
                'course_affiliate_item_id' => $product->id,
                'course_affiliate_user_id' => $affiliateCode->user_id,
            ]);
        }

        $url = $product ? $product->getUrl() : '/products';
        return redirect($url);
    }

    public function eventReferral($code, $slug)
    {
        $affiliateCode = AffiliateCode::where('code', $code)->first();
        $event = Event::where('slug', $slug)
            ->where('status', 'publish')
            ->first();

        if (!empty($affiliateCode) && !empty($event)) {
            session([
                'course_affiliate_code' => $code,
                'course_affiliate_type' => 'event',
                'course_affiliate_item_id' => $event->id,
                'course_affiliate_user_id' => $affiliateCode->user_id,
            ]);
        }

        $url = $event ? $event->getUrl() : '/events';
        return redirect($url);
    }
}
