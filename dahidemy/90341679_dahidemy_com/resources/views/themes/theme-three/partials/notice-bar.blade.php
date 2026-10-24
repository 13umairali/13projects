@if (!empty($settings['header_notice']))
    <div class="relative z-[70] overflow-hidden text-white bg-brand">
        <div class="flex items-center max-w-7xl mx-auto">
            <div class="hidden sm:flex items-center gap-2 bg-black/20 px-5 py-2.5 font-semibold whitespace-nowrap">
                <i class="fa-solid fa-bullhorn text-xs"></i>
                Latest Notice
            </div>
            <div class="flex-1 marquee py-2.5">
                <div class="marquee-track">
                    <span class="mx-10 text-sm sm:text-base">{{ $settings['header_notice'] }}</span>
                </div>
            </div>
        </div>
    </div>
@endif
