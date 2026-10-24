<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'enabled', 'registration_enabled', 'require_invite_code', 'require_admin_approval',
        'allow_cancellations', 'return_window_days', 'auto_approve_reviews',
        'store_name', 'theme', 'theme_options', 'logo_path', 'favicon_path',
        'primary_color', 'secondary_color', 'font_family',
        'hero_title', 'hero_subtitle', 'hero_image_path',
        'homepage_lineup', 'homepage_layout', 'social_links', 'menus',
        'default_warehouse_id', 'allow_overselling', 'hide_out_of_stock', 'hide_prices_for_guests', 'show_stock', 'currency_code', 'language',
        'store_url_path', 'store_use_root_domain', 'payment_stripe_enabled', 'payment_cod_enabled', 'payment_mobile_money_enabled',
        'payment_razorpay_enabled', 'payment_bkash_enabled', 'payment_sslcommerz_enabled',
        'payment_paymob_enabled', 'payment_myfatoorah_enabled', 'payment_moyasar_enabled',
        'payment_xpay_enabled', 'payment_payfast_enabled', 'payment_ligdicash_enabled',
        'payment_dodo_enabled', 'payment_waafipay_enabled',
        'payment_paypal_enabled', 'payment_paystack_enabled', 'payment_flutterwave_enabled',
        // Manual / offline methods
        'payment_gcash_enabled', 'gcash_account_name', 'gcash_account_number', 'gcash_qr_path', 'gcash_instructions',
        'payment_bank_transfer_enabled', 'bank_name', 'bank_account_name', 'bank_account_number', 'bank_branch', 'bank_instructions',
        'payment_cash_on_pickup_enabled', 'pickup_instructions',
        'contact_email', 'contact_phone', 'contact_address',
        'seo_meta_title', 'seo_meta_description',
        'topbar_text_left', 'topbar_text_right', 'footer_text',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'store_use_root_domain' => 'boolean',
        'registration_enabled' => 'boolean',
        'require_invite_code' => 'boolean',
        'require_admin_approval' => 'boolean',
        'allow_cancellations' => 'boolean',
        'return_window_days' => 'integer',
        'auto_approve_reviews' => 'boolean',
        'allow_overselling' => 'boolean',
        'hide_out_of_stock' => 'boolean',
        'hide_prices_for_guests' => 'boolean',
        'show_stock' => 'boolean',
        'payment_stripe_enabled' => 'boolean',
        'payment_cod_enabled' => 'boolean',
        'payment_mobile_money_enabled' => 'boolean',
        'payment_razorpay_enabled' => 'boolean',
        'payment_bkash_enabled' => 'boolean',
        'payment_sslcommerz_enabled' => 'boolean',
        'payment_paymob_enabled' => 'boolean',
        'payment_myfatoorah_enabled' => 'boolean',
        'payment_moyasar_enabled' => 'boolean',
        'payment_xpay_enabled' => 'boolean',
        'payment_payfast_enabled' => 'boolean',
        'payment_ligdicash_enabled' => 'boolean',
        'payment_dodo_enabled' => 'boolean',
        'payment_waafipay_enabled' => 'boolean',
        'payment_paypal_enabled' => 'boolean',
        'payment_paystack_enabled' => 'boolean',
        'payment_flutterwave_enabled' => 'boolean',
        'payment_gcash_enabled' => 'boolean',
        'payment_bank_transfer_enabled' => 'boolean',
        'payment_cash_on_pickup_enabled' => 'boolean',
        'homepage_lineup' => 'array',
        'theme_options' => 'array',
        'social_links' => 'array',
        'menus' => 'array',
    ];

    /**
     * Storefront payment-method switches (Online Store -> Payment Gateway),
     * keyed by the checkout's payment_method value. A method with no switch of
     * its own, and a tenant whose column is still NULL, count as ON so nothing
     * disappears from a checkout that was already offering it.
     */
    public const PAYMENT_METHOD_COLUMNS = [
        'credit_card'  => 'payment_stripe_enabled',
        'cod'          => 'payment_cod_enabled',
        'mobile_money' => 'payment_mobile_money_enabled',
        'razorpay'     => 'payment_razorpay_enabled',
        'bkash'        => 'payment_bkash_enabled',
        'sslcommerz'   => 'payment_sslcommerz_enabled',
        'paymob'       => 'payment_paymob_enabled',
        'myfatoorah'   => 'payment_myfatoorah_enabled',
        'moyasar'      => 'payment_moyasar_enabled',
        'xpay'         => 'payment_xpay_enabled',
        'payfast'      => 'payment_payfast_enabled',
        'ligdicash'    => 'payment_ligdicash_enabled',
        'dodo'         => 'payment_dodo_enabled',
        'waafipay'     => 'payment_waafipay_enabled',
        'paypal'       => 'payment_paypal_enabled',
        'paystack'     => 'payment_paystack_enabled',
        'flutterwave'  => 'payment_flutterwave_enabled',

        // Manual/offline methods are the exception to the "NULL counts as ON"
        // rule below: their columns default to 0, so they stay hidden until the
        // admin has entered the account details to pay into.
        'gcash'          => 'payment_gcash_enabled',
        'bank_transfer'  => 'payment_bank_transfer_enabled',
        'cash_on_pickup' => 'payment_cash_on_pickup_enabled',
    ];

    /** Offline methods the admin must configure before they can be offered. */
    public const MANUAL_METHODS = ['gcash', 'bank_transfer', 'cash_on_pickup'];

    /**
     * The warehouses the online store sells from: the configured default when
     * it is still live, otherwise every warehouse. Mirrors
     * CheckoutService::activeWarehouseIds() without its empty-set exception.
     *
     * @return int[]
     */
    public static function storeWarehouseIds(): array
    {
        $all = Warehouse::whereNull('deleted_at')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $default = (int) (static::query()->value('default_warehouse_id') ?? 0);

        return ($default && in_array($default, $all, true)) ? [$default] : $all;
    }

    /** Is this payment method offered at the storefront checkout? */
    public static function paymentMethodEnabled(string $method, ?self $settings = null): bool
    {
        $column = self::PAYMENT_METHOD_COLUMNS[$method] ?? null;
        if (! $column) {
            return true;
        }

        $settings = $settings ?: static::first();

        // Manual methods are opt-in: an absent column (tenant not migrated yet)
        // means "not configured", never "on".
        if (in_array($method, self::MANUAL_METHODS, true)) {
            return (bool) ($settings?->{$column} ?? false);
        }

        return (bool) ($settings?->{$column} ?? true);
    }

    /**
     * A menu item's label in the visitor's language. Labels live inside the
     * `menus` json as an optional `label_translations` map on each item.
     */
    public static function menuItemLabel(array $item, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $translations = $item['label_translations'] ?? [];
        if (is_string($translations)) {
            $translations = json_decode($translations, true);
        }
        $value = trim((string) (is_array($translations) ? ($translations[$locale] ?? '') : ''));

        return $value !== '' ? $value : (string) ($item['label'] ?? '');
    }

    /**
     * Resolve a menu item ({type, value}) to a storefront URL.
     * Types: home, shop, flash_sales, contact, wishlist, compare, page, collection, url.
     * Routes that this storefront does not define fall back to the raw value.
     */
    public static function menuItemUrl(array $item): string
    {
        $type = (string) ($item['type'] ?? 'url');
        $value = trim((string) ($item['value'] ?? ''));

        try {
            switch ($type) {
                case 'home': return route('store.index');
                case 'shop': return route('store.shop');
                case 'flash_sales': return route('store.flash_sales');
                case 'contact': return route('store.contact');
                case 'wishlist': return route('store.wishlist');
                case 'compare': return route('store.compare');
                case 'page': return $value !== '' ? route('store.page', $value) : '#';
                case 'collection': return $value !== '' ? route('store.shop', ['collection' => $value]) : route('store.shop');
                case 'url':
                default:
                    return $value !== '' ? $value : '#';
            }
        } catch (\Throwable $e) {
            return $value !== '' ? $value : '#';
        }
    }
}
