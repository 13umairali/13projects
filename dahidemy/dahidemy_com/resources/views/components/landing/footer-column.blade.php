@props(['title' => '', 'links' => []])

<div>
    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
    <ul class="mt-4 space-y-3">
        @foreach ($links as $link)
            <li>
                <a href="{{ isset($link['param']) ? route($link['route'], $link['param']) : route($link['route']) }}"
                    class="text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-300">
                    {{ $link['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
