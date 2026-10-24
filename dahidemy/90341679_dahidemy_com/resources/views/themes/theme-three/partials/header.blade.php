@php
    /*
     * FIX: the previous theme queried Page::where('institute_id', 1)->where('branch_id', 1')
     * with hard-coded IDs — every tenant's academics dropdown showed institute #1's pages.
     * Here we resolve the *actual* institute from the current request/domain, same helper
     * used across the rest of the app, so this partial is safe to include on any page.
     */
    $navInstitute = \App\Helpers\InstituteHelper::resolveInstituteFromRequest(request());
    $navPages = $navInstitute
        ? \Modules\Frontend\Models\Page::where('institute_id', $navInstitute->id)->get()
        : collect();

    /*
     * Custom menu builder (Modules\MenuBuilder) — if an admin has built their
     * own menu, use it. If they haven't (empty, or the module isn't
     * installed), fall through to the site's default hardcoded nav below so
     * nothing ever breaks or goes blank.
     */
    $customMenuItems = ($navInstitute && class_exists(\Modules\MenuBuilder\Models\CustomMenu::class))
        ? \Modules\MenuBuilder\Models\CustomMenu::where('institute_id', $navInstitute->id)
            ->whereNull('parent_id')
            ->where('status', \Modules\MenuBuilder\Models\CustomMenu::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->with(['children'])
            ->get()
        : collect();
@endphp

<header id="siteHeader" class="sticky top-0 z-[60] transition-all duration-300 bg-[var(--paper-50)]/95 backdrop-blur-xl border-b border-[var(--line-200)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-20">

            {{-- LOGO --}}
            <a href="{{ url('/') }}" class="flex items-center shrink-0">
                @if (!empty($instituteImage?->header_logo_light_theme))
                    <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_light_theme) }}"
                        class="h-12 md:h-14 max-w-[220px] object-contain" alt="{{ $settings['school_name'] ?? 'School logo' }}">
                @else
                    <img src="{{ asset('assets/media/logos/default.png') }}" class="h-12 md:h-14 max-w-[220px] object-contain" alt="Logo">
                @endif
            </a>

            {{-- DESKTOP NAV --}}
            <nav class="hidden lg:flex items-center gap-8 text-[15px] font-medium text-slate-700">
                @if ($customMenuItems->isNotEmpty())
                    {{-- ===================== CUSTOM MENU (admin-built) ===================== --}}
                    @foreach ($customMenuItems as $item)
                        @if ($item->link_type === 'academics_pages')
                            <div class="relative group">
                                <button class="flex items-center gap-1.5 hover:text-brand transition">
                                    {{ $item->label }} <i class="fa-solid fa-chevron-down text-[10px] mt-0.5"></i>
                                </button>
                                <div class="absolute left-0 top-full pt-3 w-60 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300">
                                    <div class="bg-white rounded-md shadow-sm border border-[var(--line-200)] py-2 overflow-hidden">
                                        @forelse ($navPages as $page)
                                            <a href="{{ route('website.page', $page->slug) }}" class="block px-5 py-3 text-sm hover:bg-slate-50 hover:text-brand transition">{{ $page->title }}</a>
                                        @empty
                                            <a href="#courses" class="block px-5 py-3 text-sm hover:bg-slate-50 hover:text-brand transition">Our Courses</a>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @elseif ($item->children->isNotEmpty())
                            <div class="relative group">
                                <button class="flex items-center gap-1.5 hover:text-brand transition">
                                    {{ $item->label }} <i class="fa-solid fa-chevron-down text-[10px] mt-0.5"></i>
                                </button>
                                <div class="absolute left-0 top-full pt-3 w-60 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300">
                                    <div class="bg-white rounded-md shadow-sm border border-[var(--line-200)] py-2 overflow-hidden">
                                        @foreach ($item->children as $child)
                                            <a href="{{ $child->resolveUrl() }}" @if ($child->open_new_tab) target="_blank" @endif class="block px-5 py-3 text-sm hover:bg-slate-50 hover:text-brand transition">{{ $child->label }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ $item->resolveUrl() }}" @if ($item->open_new_tab) target="_blank" @endif class="nav-link hover:text-brand transition">{{ $item->label }}</a>
                        @endif
                    @endforeach
                @else
                    {{-- ===================== DEFAULT MENU (unchanged) ===================== --}}
                    <a href="{{ url('/') }}" class="nav-link hover:text-brand transition">Home</a>
                    <a href="#about" class="nav-link hover:text-brand transition">About</a>

                    <div class="relative group">
                        <button class="flex items-center gap-1.5 hover:text-brand transition">
                            Academics <i class="fa-solid fa-chevron-down text-[10px] mt-0.5"></i>
                        </button>
                        <div class="absolute left-0 top-full pt-3 w-60 opacity-0 invisible translate-y-2 group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 transition-all duration-300">
                            <div class="bg-white rounded-md shadow-sm border border-[var(--line-200)] py-2 overflow-hidden">
                                @forelse ($navPages as $page)
                                    <a href="{{ route('website.page', $page->slug) }}" class="block px-5 py-3 text-sm hover:bg-slate-50 hover:text-brand transition">{{ $page->title }}</a>
                                @empty
                                    <a href="#courses" class="block px-5 py-3 text-sm hover:bg-slate-50 hover:text-brand transition">Our Courses</a>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @if (($settings['admission_display_status'] ?? null) == 'yes')
                        <a href="{{ route('admission-form-page') }}" class="nav-link hover:text-brand transition">Admission</a>
                    @endif

                    @if (($settings['exam_result_status'] ?? null) == 'yes')
                        <a href="{{ route('result-page') }}" class="nav-link hover:text-brand transition">Results</a>
                    @endif

                    <a href="{{ route('academic-images') }}" class="nav-link hover:text-brand transition">Gallery</a>
                    @if (Route::has('courses-page.index'))
                    <a href="{{ route('courses-page.index') }}" class="nav-link hover:text-brand transition">Courses</a>
                @endif
                <a href="{{ Route::has('eventenrollment.index') ? route('eventenrollment.index') : url('/#events') }}" class="nav-link hover:text-brand transition">Events</a>
                    @if (Route::has('public-form.index'))
                        <a href="{{ route('public-form.index') }}" class="nav-link hover:text-brand transition">Forms</a>
                    @endif
                    @if (Route::has('blog-page.index'))
                        <a href="{{ route('blog-page.index') }}" class="nav-link hover:text-brand transition">Blog</a>
                    @endif
                    <a href="#faq" class="nav-link hover:text-brand transition">FAQ</a>
                    <a href="{{ route('contact') }}" class="nav-link hover:text-brand transition">Contact</a>
                @endif
            </nav>

            {{-- RIGHT SIDE --}}
            <div class="hidden lg:flex items-center gap-5">
                @if (!empty($settings['phone']))
                    <a href="tel:{{ $settings['phone'] }}" class="flex items-center gap-3 text-slate-700">
                        <span class="w-11 h-11 rounded-full flex items-center justify-center" style="background:var(--brass-50); color:var(--brass-600);">
                            <i class="fa-solid fa-phone"></i>
                        </span>
                        <span class="leading-tight text-left">
                            <span class="block text-[11px] text-slate-400">Call Us</span>
                            <span class="block font-semibold text-sm">{{ $settings['phone'] }}</span>
                        </span>
                    </a>
                @endif

                <a href="{{ url('/login') }}" class="btn-secondary !py-2.5 !px-6 !text-sm">
                    Login <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </a>
            </div>

            {{-- MOBILE TOGGLE --}}
            <button id="openMobile" class="lg:hidden w-11 h-11 rounded-md flex items-center justify-center text-xl" style="background:var(--brass-50); color:var(--brass-600);">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>

{{-- MOBILE OVERLAY + SIDEBAR --}}
<div id="mobileOverlay" class="fixed inset-0 bg-black/55 z-[80] hidden"></div>

<div id="mobileSidebar" class="fixed top-0 left-[-320px] w-[300px] h-full bg-white z-[90] transition-all duration-300 border-r border-[var(--line-200)] flex flex-col">
    <div class="flex items-center justify-between p-5 border-b">
        <h3 class="font-bold text-lg">Menu</h3>
        <button id="closeMobile" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="flex flex-col gap-1 p-4 overflow-y-auto text-slate-700">
        @if ($customMenuItems->isNotEmpty())
            {{-- ===================== CUSTOM MENU (admin-built) ===================== --}}
            @foreach ($customMenuItems as $index => $item)
                @if ($item->link_type === 'academics_pages')
                    <button class="mobile-dropdown-toggle flex items-center justify-between px-3 py-3 rounded-xl hover:bg-slate-50 font-medium" data-target="mobileDropdown{{ $index }}">
                        {{ $item->label }} <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                    <div id="mobileDropdown{{ $index }}" class="hidden pl-5 flex flex-col">
                        @forelse ($navPages as $page)
                            <a href="{{ route('website.page', $page->slug) }}" class="px-3 py-2.5 text-sm text-slate-500 hover:text-brand">{{ $page->title }}</a>
                        @empty
                            <a href="#courses" class="px-3 py-2.5 text-sm text-slate-500 hover:text-brand">Our Courses</a>
                        @endforelse
                    </div>
                @elseif ($item->children->isNotEmpty())
                    <button class="mobile-dropdown-toggle flex items-center justify-between px-3 py-3 rounded-xl hover:bg-slate-50 font-medium" data-target="mobileDropdown{{ $index }}">
                        {{ $item->label }} <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>
                    <div id="mobileDropdown{{ $index }}" class="hidden pl-5 flex flex-col">
                        @foreach ($item->children as $child)
                            <a href="{{ $child->resolveUrl() }}" @if ($child->open_new_tab) target="_blank" @endif class="px-3 py-2.5 text-sm text-slate-500 hover:text-brand">{{ $child->label }}</a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ $item->resolveUrl() }}" @if ($item->open_new_tab) target="_blank" @endif class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">{{ $item->label }}</a>
                @endif
            @endforeach
        @else
            {{-- ===================== DEFAULT MENU (unchanged) ===================== --}}
            <a href="{{ url('/') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Home</a>
            <a href="#about" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">About</a>

            @if (($settings['admission_display_status'] ?? null) == 'yes')
                <a href="{{ route('admission-form-page') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Admission</a>
            @endif

            @if (($settings['exam_result_status'] ?? null) == 'yes')
                <a href="{{ route('result-page') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Results</a>
            @endif

            <a href="{{ route('academic-images') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Gallery</a>

            <button id="mobileAcademics" class="flex items-center justify-between px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">
                Academics <i class="fa-solid fa-chevron-down text-xs"></i>
            </button>
            <div id="mobileAcademicBox" class="hidden pl-5 flex flex-col">
                @forelse ($navPages as $page)
                    <a href="{{ route('website.page', $page->slug) }}" class="px-3 py-2.5 text-sm text-slate-500 hover:text-brand">{{ $page->title }}</a>
                @empty
                    <a href="#courses" class="px-3 py-2.5 text-sm text-slate-500 hover:text-brand">Our Courses</a>
                @endforelse
            </div>

            @if (Route::has('courses-page.index'))
            <a href="{{ route('courses-page.index') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Courses</a>
        @endif
        <a href="{{ Route::has('eventenrollment.index') ? route('eventenrollment.index') : url('/#events') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Events</a>
            @if (Route::has('public-form.index'))
                <a href="{{ route('public-form.index') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Forms</a>
            @endif
            @if (Route::has('blog-page.index'))
                <a href="{{ route('blog-page.index') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Blog</a>
            @endif
            <a href="#faq" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">FAQ</a>
            <a href="{{ route('contact') }}" class="px-3 py-3 rounded-xl hover:bg-slate-50 font-medium">Contact</a>
        @endif
    </div>

    <div class="p-4 border-t">
        <a href="{{ url('/login') }}" class="btn-secondary w-full justify-center">
            Login <i class="fa-solid fa-arrow-right-to-bracket"></i>
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const header = document.getElementById('siteHeader');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 12) {
                header.classList.add('shadow-lg');
            } else {
                header.classList.remove('shadow-lg');
            }
        });

        const open = document.getElementById('openMobile');
        const close = document.getElementById('closeMobile');
        const side = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('mobileOverlay');

        const showSidebar = () => {
            side.style.left = '0';
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };
        const hideSidebar = () => {
            side.style.left = '-320px';
            overlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
        };

        open?.addEventListener('click', showSidebar);
        close?.addEventListener('click', hideSidebar);
        overlay?.addEventListener('click', hideSidebar);

        // Default menu's Academics toggle
        document.getElementById('mobileAcademics')?.addEventListener('click', () => {
            document.getElementById('mobileAcademicBox')?.classList.toggle('hidden');
        });

        // Custom menu's dropdown toggles (one per admin-built dropdown item)
        document.querySelectorAll('.mobile-dropdown-toggle').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById(btn.dataset.target)?.classList.toggle('hidden');
            });
        });
    });
</script>
