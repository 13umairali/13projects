@if (!empty($settings['header_notice']))
    <div class="bg-ink text-white text-sm">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-3">
            <span class="uppercase tracking-wide text-xs font-semibold opacity-70">Notice</span>
            <span class="opacity-90">{{ $settings['header_notice'] }}</span>
        </div>
    </div>
@endif
