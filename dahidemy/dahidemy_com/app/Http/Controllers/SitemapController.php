<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $routes = [
            ['name' => 'index', 'priority' => '1.0', 'freq' => 'weekly'],
            ['name' => 'features', 'priority' => '0.9', 'freq' => 'monthly'],
            ['name' => 'pricing', 'priority' => '0.9', 'freq' => 'monthly'],
            ['name' => 'faq', 'priority' => '0.7', 'freq' => 'monthly'],
            ['name' => 'about', 'priority' => '0.6', 'freq' => 'monthly'],
            ['name' => 'contact', 'priority' => '0.6', 'freq' => 'monthly'],
        ];

        // W3C date (YYYY-MM-DD) shared by every entry — a valid, stable
        // <lastmod> without inventing per-page timestamps we don't track.
        $lastmod = now()->toDateString();

        $urls = collect($routes)->map(fn ($r) => [
            'loc' => route($r['name']),
            'priority' => $r['priority'],
            'freq' => $r['freq'],
            'lastmod' => $lastmod,
        ]);

        foreach (['privacy', 'terms', 'cookie'] as $type) {
            $urls->push(['loc' => route('policy', $type), 'priority' => '0.4', 'freq' => 'yearly', 'lastmod' => $lastmod]);
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /forgot-password',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}