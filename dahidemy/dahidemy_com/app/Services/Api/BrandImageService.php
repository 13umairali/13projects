<?php

namespace App\Services\Api;

use Illuminate\Http\UploadedFile;

/**
 * The institute image SaaS settings — the single source of every logo in the
 * system: admin sidebar, landing navbar, footer (light + dark) and the banner.
 *
 * The backend exposes this as an apiResource, but it behaves like a singleton:
 * index() returns InstituteImageSAASSetting::first(), so there is at most one
 * row. save() therefore creates it the first time and updates it thereafter.
 *
 * Only the files the admin actually picked are sent. The backend repository
 * replaces an image only when it receives an UploadedFile — a key that is
 * absent keeps its current value (and an omitted key is how you keep the old
 * logo), while a present-but-empty key would blank the column.
 */
class BrandImageService extends BaseApiService
{
    public const ENDPOINT = 'institute-image-saas-settings';

    /** The five image columns, in the order they appear in the admin form. */
    public const FIELDS = [
        'header_logo_light_theme',
        'header_logo_dark_theme',
        'footer_logo_light_theme',
        'footer_logo_dark_theme',
        'banner_image',
    ];

    /** The current settings row, or [] when none has been created yet. */
    public function current(): array
    {
        $res = $this->cachedGet(self::ENDPOINT);

        return $res->successful() && is_array($res->data()) ? $res->data() : [];
    }

    /**
     * Create the row on first save, update it afterwards.
     *
     * @param  array<string, UploadedFile>  $files
     */
    public function save(array $files, int|string|null $id = null): ApiResponse
    {
        return $id
            ? $this->upload(self::ENDPOINT."/{$id}", $files, 'put')
            : $this->upload(self::ENDPOINT, $files);
    }
}
