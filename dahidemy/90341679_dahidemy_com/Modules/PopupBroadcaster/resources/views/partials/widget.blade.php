@php
    $popupInstitute = \App\Helpers\InstituteHelper::resolveInstituteFromRequest(request());
    $activePopups = [];

    if ($popupInstitute) {
        $scheduled = \Modules\PopupBroadcaster\Models\Popup::currentlyScheduled($popupInstitute->id)->get();
        $popupUserType = auth()->check() ? auth()->user()->user_type : null;
        $activePopups = \Modules\PopupBroadcaster\Models\Popup::visibleToUserType($scheduled, $popupUserType);
    }
@endphp

@if (!empty($activePopups))
    <div id="popupBroadcasterOverlay" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        @foreach ($activePopups as $index => $popup)
            <div class="popup-broadcaster-item {{ $index === 0 ? '' : 'hidden' }} bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden relative"
                data-popup-id="{{ $popup->id }}" data-frequency="{{ $popup->display_frequency }}" data-updated="{{ optional($popup->updated_at)->timestamp }}">

                <button type="button" class="popup-broadcaster-close absolute top-4 right-4 w-9 h-9 rounded-full bg-black/5 hover:bg-black/10 flex items-center justify-center text-slate-500 z-10">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                @if ($popup->image)
                    <img src="{{ asset('storage/popups/' . $popup->image) }}" class="w-full h-48 object-cover" alt="{{ $popup->title }}">
                @endif

                <div class="p-8">
                    <h3 class="text-xl font-bold mb-3" style="font-family:'Sora',sans-serif;">{{ $popup->title }}</h3>
                    @if ($popup->message)
                        <p class="text-slate-600 leading-7 mb-6">{{ $popup->message }}</p>
                    @endif
                    @if ($popup->button_text && $popup->button_link)
                        <a href="{{ $popup->button_link }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full text-white font-semibold"
                            style="background: linear-gradient(120deg, #4f46e5, #7c3aed);">
                            {{ $popup->button_text }} <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const overlay = document.getElementById('popupBroadcasterOverlay');
            if (!overlay) return;
            const items = Array.from(overlay.querySelectorAll('.popup-broadcaster-item'));

            function storageKeyFor(item) {
                return 'popup_seen_' + item.dataset.popupId + '_' + item.dataset.updated;
            }

            function alreadySeen(item) {
                const key = storageKeyFor(item);
                const freq = item.dataset.frequency;
                if (freq === 'every_visit') return false;
                if (freq === 'once_ever') return localStorage.getItem(key) === '1';
                return sessionStorage.getItem(key) === '1'; // once_per_session
            }

            function markSeen(item) {
                const key = storageKeyFor(item);
                const freq = item.dataset.frequency;
                if (freq === 'once_ever') localStorage.setItem(key, '1');
                else if (freq === 'once_per_session') sessionStorage.setItem(key, '1');
            }

            const queue = items.filter((item) => !alreadySeen(item));
            if (queue.length === 0) return;

            let currentIndex = 0;
            function showCurrent() {
                items.forEach((i) => i.classList.add('hidden'));
                queue[currentIndex].classList.remove('hidden');
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
            function closeCurrent() {
                markSeen(queue[currentIndex]);
                currentIndex++;
                if (currentIndex < queue.length) {
                    showCurrent();
                } else {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                }
            }

            overlay.querySelectorAll('.popup-broadcaster-close').forEach((btn) => {
                btn.addEventListener('click', closeCurrent);
            });
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeCurrent();
            });

            setTimeout(showCurrent, 1200);
        });
    </script>
@endif
