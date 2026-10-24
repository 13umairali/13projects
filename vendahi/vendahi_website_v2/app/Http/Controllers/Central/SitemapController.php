<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\BlogPost;
use App\Services\LandingCmsService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        $urls[] = ['loc' => route('central.welcome')];

        // Privacy / terms 404 when their CMS section is missing or inactive.
        try {
            $cms = app(LandingCmsService::class);
            $privacy = $cms->getSection('privacy_policy');
            if ($privacy && $privacy->is_active) {
                $urls[] = ['loc' => route('central.privacy-policy')];
            }
            $terms = $cms->getSection('terms_conditions');
            if ($terms && $terms->is_active) {
                $urls[] = ['loc' => route('central.terms-conditions')];
            }
        } catch (\Throwable $e) {
            // CMS tables unavailable — skip the optional pages.
        }

        // Blog — tolerate missing tables (fresh install before central migrations).
        try {
            $posts = BlogPost::published()
                ->orderByDesc('published_at')
                ->get(['slug', 'updated_at']);

            if ($posts->isNotEmpty()) {
                $urls[] = ['loc' => route('central.blog.index')];
            }

            foreach ($posts as $post) {
                $urls[] = [
                    'loc'     => route('central.blog.show', $post->slug),
                    'lastmod' => $post->updated_at?->toW3cString(),
                ];
            }
        } catch (\Throwable $e) {
            // Blog tables unavailable — serve the static URLs only.
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . e($url['loc']) . "</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . e($url['lastmod']) . "</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
