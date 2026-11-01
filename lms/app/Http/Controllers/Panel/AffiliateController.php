<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\Affiliate;
use App\Models\AffiliateCode;
use App\Models\Bundle;
use App\Models\CourseAffiliateLink;
use App\Models\Event;
use App\Models\Product;
use App\Models\Webinar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("panel_marketing_affiliates");

        $user = auth()->user();

        $query = Affiliate::query()->where('affiliate_user_id', $user->id);

        $getListData = $this->getListsData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $affiliateCode = $user->affiliateCode;

        if (empty($affiliateCode)) {
            $affiliateCode = $this->makeUserAffiliateCode($user);
        }

        $referredUsersCount = Affiliate::where('affiliate_user_id', $user->id)->count();

        $registrationBonus = Accounting::where('is_affiliate_amount', true)
            ->where('system', false)
            ->where('user_id', $user->id)
            ->sum('amount');

        $affiliateBonus = Accounting::where('is_affiliate_commission', true)
            ->where('system', false)
            ->where('user_id', $user->id)
            ->sum('amount');

        $totalAffiliateLinkEarnings = $affiliateBonus;

        $referralHowWorkSettings = getReferralHowWorkSettings();

        $data = [
            'pageTitle' => trans('panel.affiliates_page'),
            'affiliateCode' => $affiliateCode,
            'registrationBonus' => $registrationBonus,
            'affiliateBonus' => $affiliateBonus,
            'totalAffiliateLinkEarnings' => $totalAffiliateLinkEarnings,
            'referredUsersCount' => $referredUsersCount,
            'referralHowWorkSettings' => $referralHowWorkSettings,
            'activeTab' => 'referred_users',
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.marketing.affiliates.index', $data);
    }

    public function courses(Request $request)
    {
        $this->authorize("panel_marketing_affiliates");

        $user = auth()->user();

        $affiliateCode = $user->affiliateCode;

        if (empty($affiliateCode)) {
            $affiliateCode = $this->makeUserAffiliateCode($user);
        }

        // Get all user affiliate links
        $userLinks = CourseAffiliateLink::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all affiliate commission stats for this user
        $affiliateStats = Accounting::where('user_id', $user->id)
            ->where('is_affiliate_commission', true)
            ->get();

        $affiliateItems = [];

        foreach ($userLinks as $link) {
            $item = $link->getItem();
            if (!empty($item)) {
                // Calculate stats based on type
                $salesCount = 0;
                $totalEarnings = 0;

                switch ($link->type) {
                    case CourseAffiliateLink::TYPE_COURSE:
                        $stats = $affiliateStats->where('webinar_id', $link->item_id);
                        $salesCount = $stats->count();
                        $totalEarnings = $stats->sum('amount');
                        break;
                    case CourseAffiliateLink::TYPE_BUNDLE:
                        $stats = $affiliateStats->where('bundle_id', $link->item_id);
                        $salesCount = $stats->count();
                        $totalEarnings = $stats->sum('amount');
                        break;
                    case CourseAffiliateLink::TYPE_PRODUCT:
                        $stats = $affiliateStats->where('product_id', $link->item_id);
                        $salesCount = $stats->count();
                        $totalEarnings = $stats->sum('amount');
                        break;
                    case CourseAffiliateLink::TYPE_EVENT:
                        $eventTicketIds = \App\Models\EventTicket::where('event_id', $link->item_id)->pluck('id')->toArray();
                        $stats = $affiliateStats->whereIn('event_ticket_id', $eventTicketIds);
                        $salesCount = $stats->count();
                        $totalEarnings = $stats->sum('amount');
                        break;
                }

                $affiliateItems[] = [
                    'linkId' => $link->id,
                    'type' => $link->type,
                    'typeLabel' => CourseAffiliateLink::getTypeLabel($link->type),
                    'item' => $item,
                    'affiliateUrl' => $link->getAffiliateUrl($affiliateCode->code),
                    'salesCount' => $salesCount,
                    'totalEarnings' => $totalEarnings,
                ];
            }
        }

        $referralSettings = getReferralSettings();
        $referralHowWorkSettings = getReferralHowWorkSettings();

        // Stats for top bar
        $referredUsersCount = Affiliate::where('affiliate_user_id', $user->id)->count();

        $registrationBonus = Accounting::where('is_affiliate_amount', true)
            ->where('system', false)
            ->where('user_id', $user->id)
            ->sum('amount');

        $affiliateBonus = Accounting::where('is_affiliate_commission', true)
            ->where('system', false)
            ->where('user_id', $user->id)
            ->sum('amount');

        $totalAffiliateLinkEarnings = $affiliateStats->sum('amount');

        $data = [
            'pageTitle' => trans('panel.affiliates_page'),
            'affiliateCode' => $affiliateCode,
            'affiliateItems' => $affiliateItems,
            'referralSettings' => $referralSettings,
            'referralHowWorkSettings' => $referralHowWorkSettings,
            'referredUsersCount' => $referredUsersCount,
            'registrationBonus' => $registrationBonus,
            'affiliateBonus' => $affiliateBonus,
            'totalAffiliateLinkEarnings' => $totalAffiliateLinkEarnings,
            'activeTab' => 'courses',
        ];

        return view('design_1.panel.marketing.affiliates.index', $data);
    }

    public function getGenerateLinkModal()
    {
        $this->authorize("panel_marketing_affiliates");

        $html = (string) view()->make('design_1.panel.marketing.affiliates.modals.generate_link');

        return response()->json([
            'code' => 200,
            'html' => $html,
        ]);
    }

    public function searchCourses(Request $request)
    {
        $this->authorize("panel_marketing_affiliates");

        $term = $request->get('term', '');
        $type = $request->get('type', CourseAffiliateLink::TYPE_COURSE);

        $result = [];

        switch ($type) {
            case CourseAffiliateLink::TYPE_BUNDLE:
                $items = Bundle::select('id', 'teacher_id')
                    ->where('status', Bundle::$active)
                    ->where('price', '>', 0)
                    ->whereTranslationLike('title', "%$term%")
                    ->limit(20)
                    ->get();
                break;

            case CourseAffiliateLink::TYPE_PRODUCT:
                $items = Product::select('id', 'creator_id')
                    ->where('status', Product::$active)
                    ->where('price', '>', 0)
                    ->whereTranslationLike('title', "%$term%")
                    ->limit(20)
                    ->get();
                break;

            case CourseAffiliateLink::TYPE_EVENT:
                $items = Event::select('id', 'creator_id')
                    ->where('status', 'publish')
                    ->whereTranslationLike('title', "%$term%")
                    ->limit(20)
                    ->get();
                break;

            case CourseAffiliateLink::TYPE_COURSE:
            default:
                $items = Webinar::select('id', 'teacher_id')
                    ->where('status', Webinar::$active)
                    ->where('price', '>', 0)
                    ->whereTranslationLike('title', "%$term%")
                    ->limit(20)
                    ->get();
                break;
        }

        foreach ($items as $item) {
            $result[] = [
                'id' => $item->id,
                'title' => $item->title,
            ];
        }

        return response()->json($result);
    }

    public function storeCourseLink(Request $request)
    {
        $this->authorize("panel_marketing_affiliates");

        $user = auth()->user();
        $itemId = $request->input('item_id');
        $type = $request->input('type', CourseAffiliateLink::TYPE_COURSE);

        // Validate type
        if (!in_array($type, CourseAffiliateLink::$types)) {
            return response()->json([
                'code' => 422,
                'errors' => ['item_id' => [trans('update.course_not_found')]]
            ], 422);
        }

        // Validate item exists based on type
        $item = null;
        switch ($type) {
            case CourseAffiliateLink::TYPE_BUNDLE:
                $item = Bundle::where('id', $itemId)->where('status', Bundle::$active)->where('price', '>', 0)->first();
                break;
            case CourseAffiliateLink::TYPE_PRODUCT:
                $item = Product::where('id', $itemId)->where('status', Product::$active)->where('price', '>', 0)->first();
                break;
            case CourseAffiliateLink::TYPE_EVENT:
                $item = Event::where('id', $itemId)->where('status', 'publish')->first();
                break;
            case CourseAffiliateLink::TYPE_COURSE:
            default:
                $item = Webinar::where('id', $itemId)->where('status', Webinar::$active)->where('price', '>', 0)->first();
                break;
        }

        if (empty($item)) {
            return response()->json([
                'code' => 422,
                'errors' => ['item_id' => [trans('update.course_not_found')]]
            ], 422);
        }

        // Check if already added
        $exists = CourseAffiliateLink::where('user_id', $user->id)
            ->where('item_id', $itemId)
            ->where('type', $type)
            ->first();

        if (!empty($exists)) {
            return response()->json([
                'code' => 422,
                'errors' => ['item_id' => [trans('update.course_already_in_affiliate_list')]]
            ], 422);
        }

        CourseAffiliateLink::create([
            'user_id' => $user->id,
            'type' => $type,
            'item_id' => $itemId,
            'created_at' => time(),
        ]);

        return response()->json([
            'code' => 200,
            'msg' => trans('public.request_success'),
        ]);
    }

    public function deleteCourseLink($id)
    {
        $this->authorize("panel_marketing_affiliates");

        $user = auth()->user();

        $link = CourseAffiliateLink::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!empty($link)) {
            $link->delete();
        }

        return response()->json([
            'code' => 200,
            'msg' => trans('public.request_success'),
        ]);
    }


    private function getListsData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $affiliates = $query
            ->with([
                'referredUser',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $affiliates, $total, $count);
        }

        return [
            'affiliates' => $affiliates,
            'pagination' => $this->makePagination($request, $affiliates, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $affiliates, $total, $count)
    {
        $html = "";

        foreach ($affiliates as $affiliateRow) {
            $html .= (string) view()->make('design_1.panel.marketing.affiliates.table_items', ['affiliate' => $affiliateRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $affiliates, $total, $count, true)
        ]);
    }

    private function makeUserAffiliateCode($user)
    {
        $code = mt_rand(100000, 999999);

        $check = AffiliateCode::where('code', $code)->first();

        if (!empty($check)) {
            return $this->makeUserAffiliateCode($user);
        }

        $affiliateCode = AffiliateCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'created_at' => time()
        ]);

        return $affiliateCode;
    }
}


