<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcommerceClient;
use App\Models\Message;
use App\Models\OnlineOrder;
use App\Models\OnlineOrderReturn;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\StoreQuoteRequest;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

/**
 * Everything waiting on a member of staff, in one payload: the notification
 * bell renders the list, the sidebar renders the same numbers as badges.
 *
 * Counts are DERIVED from the live rows (pending status / unread flag), never
 * stored — so an item disappears by itself the moment it is processed, with no
 * "mark as read" bookkeeping to drift out of sync.
 */
class PendingWorkController extends Controller
{
    use \App\Traits\ScopesWarehouseAccess;

    public function index(Request $request)
    {
        $user = $request->user('api');
        abort_unless($user, 401);

        $items = [];

        foreach ($this->sources() as $key => $source) {
            // Skip anything this user may not see, and anything whose table is
            // not present (modules differ between installs / upgrade states).
            if (! empty($source['table']) && ! Schema::hasTable($source['table'])) {
                continue;
            }
            if (! $this->allows($user, $source)) {
                continue;
            }

            try {
                $count = (int) ($source['count'])();
            } catch (\Throwable $e) {
                continue;
            }

            if ($count <= 0) {
                continue;
            }

            $items[] = [
                'key' => $key,
                // The admin SPA translates from its own bundle, so hand it the
                // key rather than a PHP-side string.
                'label_key' => $source['label'],
                'count' => $count,
                'route' => $source['route'],
                'severity' => $source['severity'] ?? 'info',
            ];
        }

        return response()->json([
            'items' => $items,
            'total' => array_sum(array_column($items, 'count')),
        ]);
    }

    /**
     * key => [count closure, label key, legacy SPA route (resolved by goTo()), permission/policy, table].
     * `permission` is a plain permission name; `policy` is [ability, class].
     */
    private function sources(): array
    {
        return [
            'quantity_alerts' => [
                'label' => 'Product_Quantity_Alerts',
                'route' => '/app/reports/quantity_alerts',
                'permission' => 'Reports_quantity_alerts',
                'table' => 'product_warehouse',
                'severity' => 'warning',
                // Same query as ReportController@count_quantity_alert: stock
                // lives on product_warehouse, and only the user's warehouses
                // count.
                'count' => fn () => \App\Models\product_warehouse::join('products', 'product_warehouse.product_id', '=', 'products.id')
                    ->whereRaw('qte <= stock_alert')
                    ->whereIn('product_warehouse.warehouse_id', $this->userWarehouseIds())
                    ->count(),
            ],
            'new_orders' => [
                'label' => 'Pending_Online_Orders',
                'route' => '/app/Store/Orders',
                'permission' => 'Orders_view',
                'table' => 'online_orders',
                'count' => fn () => OnlineOrder::where('status', 'pending')->count(),
            ],
            'payment_proofs' => [
                'label' => 'Payment_Proofs_To_Verify',
                'route' => '/app/Store/Orders?proof=pending',
                'permission' => 'Orders_view',
                'table' => 'online_order_payment_proofs',
                'count' => fn () => \App\Models\OnlineOrderPaymentProof::where('status', 'pending')->count(),
            ],
            'pending_customers' => [
                'label' => 'Pending_Customers',
                'route' => '/app/Store/PendingCustomers',
                'policy' => ['view', StoreSetting::class],
                'table' => 'ecommerce_clients',
                'count' => fn () => EcommerceClient::whereNull('deleted_at')->where('status', 0)->count(),
            ],
            'return_requests' => [
                'label' => 'Return_Requests',
                'route' => '/app/Store/Returns',
                'permission' => 'Orders_view',
                'table' => 'online_order_returns',
                'count' => fn () => OnlineOrderReturn::where('status', 'requested')->count(),
            ],
            'quote_requests' => [
                'label' => 'Quote_Requests',
                'route' => '/app/Store/QuoteRequests',
                'permission' => 'Orders_view',
                'table' => 'store_quote_requests',
                'count' => fn () => StoreQuoteRequest::where('status', 'new')->count(),
            ],
            'portal_quote_requests' => [
                // Quotations a portal customer asked for and nobody has priced
                // yet. `source` keeps staff-created drafts (also 'pending')
                // out of the count; it clears itself once the quote is sent.
                'label' => 'Portal_Quotation_Requests',
                'route' => '/app/quotations/list?source=portal&statut=pending',
                'permission' => 'Quotations_view',
                'table' => 'quotations',
                'count' => fn () => \App\Models\Quotation::whereNull('deleted_at')
                    ->where('source', 'portal')
                    ->where('statut', 'pending')
                    ->whereIn('warehouse_id', $this->userWarehouseIds())
                    // Same row scope as the list the badge links to, so the
                    // number never points at an empty page.
                    ->when(! optional(auth()->user())->hasRecordView(),
                        fn ($q) => $q->where('user_id', optional(auth()->user())->id))
                    ->count(),
            ],
            'product_reviews' => [
                'label' => 'Product_Reviews',
                'route' => '/app/Store/Reviews',
                'policy' => ['view', StoreSetting::class],
                'table' => 'product_reviews',
                'count' => fn () => ProductReview::where('status', 'pending')->count(),
            ],
            'messages' => [
                'label' => 'Messages',
                'route' => '/app/Store/Messages',
                'permission' => 'Messages_view',
                'table' => 'messages',
                'count' => fn () => Message::where('is_read', 0)->count(),
            ],
        ];
    }

    /** Permission name or policy ability, whichever the source declares. */
    private function allows($user, array $source): bool
    {
        if (! empty($source['policy'])) {
            [$ability, $class] = $source['policy'];

            return Gate::forUser($user)->allows($ability, $class);
        }

        if (! empty($source['permission'])) {
            try {
                $permission = \App\Models\Permission::where('name', $source['permission'])->first();

                return $permission ? (bool) $user->hasRole($permission->roles) : false;
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }
}
