<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\BrandImageService;
use App\Services\Api\SaasContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Logos & branding — the institute image SaaS settings.
 *
 * Every logo the system renders comes from here: the admin sidebar, the landing
 * navbar and the footer (each with a light and a dark variant), plus the banner.
 * Saving flushes the public content cache so the marketing site picks the new
 * logos up immediately.
 */
class BrandImageController extends Controller
{
    public function __construct(
        protected BrandImageService $images,
        protected SaasContentService $content,
    ) {}

    public function index(): View
    {
        return view('admin.branding.index', [
            'settings' => $this->images->current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'];

        $request->validate(array_fill_keys(BrandImageService::FIELDS, $rules));

        // Send only what was actually picked: the backend keeps the existing
        // image for any field it does not receive as an uploaded file.
        $files = [];
        foreach (BrandImageService::FIELDS as $field) {
            if ($request->hasFile($field)) {
                $files[$field] = $request->file($field);
            }
        }

        if (! $files) {
            return back()->with('error', 'Choose at least one image to upload.');
        }

        $current = $this->images->current();
        $response = $this->images->save($files, $current['id'] ?? null);

        if (! $response->successful()) {
            return back()
                ->withErrors($response->validationErrors())
                ->with('error', $response->message ?: 'Could not save the branding images.');
        }

        $this->content->flush();

        return redirect()
            ->route('admin.branding.index')
            ->with('success', 'Branding images updated.');
    }
}
