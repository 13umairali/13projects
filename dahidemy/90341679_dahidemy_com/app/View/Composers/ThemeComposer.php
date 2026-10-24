<?php

namespace App\View\Composers;

use App\Helpers\InstituteHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Authentication\Models\InstituteImageSetting;

/**
 * Shared data for every `themes.*` view (public tenant website).
 *
 * Several controller methods (policy pages, contact, onboarding, event
 * details) render these views without passing `settings`, `institute`, or
 * `instituteImage` at all, even though the layout and partials read from all
 * three unconditionally. Undefined-variable access there was silently
 * swallowed by `??`/`isset`, so the pages rendered "successfully" with a
 * blank header notice, blank contact/social links, blank footer, and no
 * favicon — not a crash, just an empty-looking site.
 *
 * This composer fills in anything a controller didn't already provide,
 * rather than replacing it, so a controller that deliberately passes richer
 * data (e.g. WebsiteController::index()) keeps full control.
 */
class ThemeComposer
{
    public function __construct(private Request $request) {}

    public function compose(View $view): void
    {
        $data = $view->getData();

        $institute = $data['institute'] ?? InstituteHelper::resolveInstituteFromRequest($this->request);
        $instituteId = $institute->id ?? null;

        if (! array_key_exists('institute', $data)) {
            $view->with('institute', $institute);
        }

        if (! array_key_exists('settings', $data)) {
            $view->with('settings', $this->settingsFor($instituteId));
        }

        if (! array_key_exists('instituteImage', $data)) {
            $view->with('instituteImage', $instituteId
                ? InstituteImageSetting::where('institute_id', $instituteId)->first()
                : null);
        }

        if (! array_key_exists('theme', $data)) {
            $view->with('theme', active_website_theme($instituteId));
        }
    }

    /**
     * @return array<string, string>
     */
    private function settingsFor(?int $instituteId): array
    {
        if (! $instituteId || ! Schema::hasTable('settings')) {
            return [];
        }

        return DB::table('settings')
            ->where('institute_id', $instituteId)
            ->pluck('value', 'name')
            ->all();
    }
}
