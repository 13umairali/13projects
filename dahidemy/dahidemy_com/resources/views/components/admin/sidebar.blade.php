@props(['brand' => []])

@php
    use App\Support\ResourceRegistry;

    // Config-driven CMS modules, grouped for the sidebar.
    $contentGroups = ResourceRegistry::grouped();
    $activeResource = request()->route('resource');

    // Live tenant-management modules (backed by real backend endpoints).
    $tenantNav = [
        [
            'route' => 'admin.institutes.index',
            'pattern' => 'admin.institutes.*',
            'label' => 'Institutes',
            'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01',
        ],
        [
            'route' => 'admin.onboarding.index',
            'pattern' => 'admin.onboarding.*',
            'label' => 'Onboarding',
            'icon' => 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 7a4 4 0 108 0 4 4 0 00-8 0zM19 8v6M22 11h-6',
        ],
        // [
        //     'route' => 'admin.domain-requests.index',
        //     'pattern' => 'admin.domain-requests.*',
        //     'label' => 'Domain requests',
        //     'icon' => 'M12 2a10 10 0 100 20 10 10 0 000-20zM2 12h20M12 2c2.5 2.7 4 6.2 4 10s-1.5 7.3-4 10c-2.5-2.7-4-6.2-4-10s1.5-7.3 4-10z',
        // ],
        [
            'route' => 'admin.backups.index',
            'pattern' => 'admin.backups.*',
            'label' => 'Backups',
            'icon' => 'M3 5v14a9 3 0 0018 0V5M3 5a9 3 0 0018 0M3 5a9 3 0 0018 0M3 12a9 3 0 0018 0',
        ],
    ];

    // Inbound messages from the public site.
    $communicationNav = [
        [
            'route' => 'admin.contacts.index',
            'pattern' => 'admin.contacts.*',
            'label' => 'Contact messages',
            'icon' => 'M4 4h16v16H4zM22 6l-10 7L2 6',
        ],
    ];

    // Billing / subscription lifecycle.
    $subscriptionNav = [
        [
            'route' => 'admin.subscriptions.requests',
            'pattern' => 'admin.subscriptions.requests*',
            'label' => 'Upgrade requests',
            'icon' => 'M2 7h20M2 7v10a2 2 0 002 2h16a2 2 0 002-2V7M6 12h4',
        ],
        [
            'route' => 'admin.subscriptions.upgrade',
            'pattern' => 'admin.subscriptions.upgrade*',
            'label' => 'Manual upgrade',
            'icon' => 'M12 19V5M5 12l7-7 7 7',
        ],
        [
            'route' => 'admin.referrals.index',
            'pattern' => 'admin.referrals.*',
            'label' => 'Referrals',
            'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75',
        ],
        [
            'route' => 'admin.withdrawal-settings.edit',
            'pattern' => 'admin.withdrawal-settings.*',
            'label' => 'Withdrawal settings',
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        ],
    ];

    // Configuration modules (backed by real backend endpoints).
    $configNav = [
        [
            'route' => 'admin.branding.index',
            'pattern' => 'admin.branding.*',
            'label' => 'Logos & branding',
            'icon' => 'M3 5h18v14H3zM3 15l4-4 4 4M14 12l2-2 5 5M16.5 9.5a1 1 0 100-2 1 1 0 000 2z',
        ],
        [
            'route' => 'admin.payments.index',
            'pattern' => 'admin.payments.*',
            'label' => 'Payment gateways',
            'icon' => 'M3 10h18M3 6h18v12H3zM7 15h2',
        ],
        [
            'route' => 'admin.settings.index',
            'pattern' => 'admin.settings.*',
            'label' => 'Settings',
            'icon' =>
                'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.6 1.6 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.6 1.6 0 00-2.7 1.1V21a2 2 0 11-4 0v-.1A1.6 1.6 0 004 19.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.6 1.6 0 00-1.1-2.7H2a2 2 0 110-4h.1A1.6 1.6 0 003.4 8.6l-.1-.1a2 2 0 112.8-2.8l.1.1a1.6 1.6 0 001.8.3H8a1.6 1.6 0 001-1.5V3a2 2 0 114 0v.1a1.6 1.6 0 001 1.5 1.6 1.6 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.6 1.6 0 00-.3 1.8V9a1.6 1.6 0 001.5 1H21a2 2 0 110 4h-.1a1.6 1.6 0 00-1.5 1z',
        ],
    ];

    $renderNav = function (array $item) {
        $isActive = request()->routeIs($item['pattern']);
        $classes =
            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition ' .
            ($isActive
                ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'
                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5');

        return '<a href="' .
            route($item['route']) .
            '"' .
            ($isActive ? ' data-active' : '') .
            ' class="' .
            $classes .
            '">' .
            '<svg class="flex-none w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="' .
            $item['icon'] .
            '"/></svg>' .
            '<span class="truncate">' .
            e($item['label']) .
            '</span></a>';
    };
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 h-screen overflow-hidden transition-transform duration-300 -translate-x-full bg-white border-r border-slate-200 lg:translate-x-0 dark:border-white/10 dark:bg-slate-900"
    :class="sidebar && 'translate-x-0'">
    {{-- Sidebar logo: the header slots from Logos & branding (institute image
         SaaS settings). Falls back to the brand mark until one is uploaded, and
         again if the uploaded file goes missing from the backend disk. --}}
    @php
        $sidebarLogoLight = $brand['header_logo_light'] ?? null;
        $sidebarLogoDark = $brand['header_logo_dark'] ?? $sidebarLogoLight;
    @endphp

    <a href="{{ route('admin.dashboard') }}"
        class="flex h-16 flex-none items-center gap-2.5 border-b border-slate-200 px-5 font-bold dark:border-white/10">
        @if ($sidebarLogoLight)
            <img src="{{ $sidebarLogoLight }}" alt="{{ $brand['name'] ?? '' }}" class="w-auto h-8 dark:hidden"
                onerror="this.onerror=null;this.remove()">
            <img src="{{ $sidebarLogoDark }}" alt="{{ $brand['name'] ?? '' }}" class="hidden w-auto h-8 dark:block"
                onerror="this.onerror=null;this.remove()">
        @else
            <span
                class="grid flex-none w-8 h-8 text-white rounded-lg place-items-center bg-gradient-to-br from-brand-500 to-accent-500">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10L12 5 2 10l10 5 10-5z" />
                    <path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5" />
                </svg>
            </span>
        @endif
        <span class="truncate">{{ $brand['name'] ?? config('saas.brand.name') }}</span>
    </a>

    {{-- min-h-0: without it a flex child keeps min-height:auto, refuses to shrink
         below its content, and overflows the aside instead of scrolling.
         overscroll-contain stops the page scrolling once the menu hits its end. --}}
    <nav class="flex-1 min-h-0 p-4 space-y-1 overflow-y-auto sidebar-scroll overscroll-contain scroll-smooth"
        x-init="$nextTick(() => {
            const a = $el.querySelector('[data-active]');
            if (a) {
                const r = a.getBoundingClientRect(),
                    n = $el.getBoundingClientRect();
                $el.scrollTop += (r.top - n.top) - (n.height - r.height) / 2;
            }
        })">
        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) data-active @endif
            @class([
                'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' => request()->routeIs(
                    'admin.dashboard'),
                'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' => !request()->routeIs(
                    'admin.dashboard'),
            ])>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path d="M3 3h8v8H3zM13 3h8v5h-8zM13 10h8v11h-8zM3 13h8v8H3z" />
            </svg>
            Dashboard
        </a>

        {{-- Tenant management (live) --}}
        <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tenant management
        </p>
        @foreach ($tenantNav as $item)
            {!! $renderNav($item) !!}
        @endforeach

        {{-- Communication (live) --}}
        <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Communication</p>
        @foreach ($communicationNav as $item)
            {!! $renderNav($item) !!}
        @endforeach

        {{-- Subscriptions (live) --}}
        <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Subscriptions</p>
        @foreach ($subscriptionNav as $item)
            {!! $renderNav($item) !!}
        @endforeach

        {{-- Config-driven CMS groups --}}
        @foreach ($contentGroups as $groupLabel => $items)
            <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                {{ $groupLabel }}</p>
            @foreach ($items as $item)
                @php $isActive = request()->routeIs('admin.content.*') && $activeResource === $item['key']; @endphp
                <a href="{{ route('admin.content.index', $item['key']) }}"
                    @if ($isActive) data-active @endif @class([
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                        'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' => $isActive,
                        'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' => !$isActive,
                    ])>
                    <svg class="flex-none w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $item['icon'] }}" />
                    </svg>
                    <span class="truncate">{{ $item['plural'] }}</span>
                </a>
            @endforeach
        @endforeach

        {{-- Configuration (live) --}}
        <p class="px-3 pb-1 pt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Configuration</p>
        @foreach ($configNav as $item)
            {!! $renderNav($item) !!}
        @endforeach
    </nav>
</aside>