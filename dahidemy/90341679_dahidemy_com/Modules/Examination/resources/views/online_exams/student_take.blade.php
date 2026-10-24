@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ $attempt->onlineExam->name }}
@endsection
@section('content')
    @if ($attempt->onlineExam->proctoring_enabled)
        <div id="integrityBanner" class="alert alert-warning d-flex align-items-center justify-content-between mb-4">
            <div>
                <i class="fa fa-shield-halved me-2"></i>
                {{ _lang('This exam is monitored. Please stay in fullscreen and on this tab — switching away, exiting fullscreen, or attempting to copy/paste is recorded for your teacher to review.') }}
            </div>
            <button type="button" class="btn btn-sm btn-warning" id="enterFullscreenBtn">
                <i class="fa fa-expand me-1"></i> {{ _lang('Enter Fullscreen') }}
            </button>
        </div>
    @endif

    <div class="card" id="examCard">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ $attempt->onlineExam->name }}</h3>
            <div class="fw-bold text-danger" id="exam-timer">--:--</div>
        </div>
        <div class="card-body">
            <form id="exam-form" method="POST" action="{{ route('student.online-exams.submit', $attempt->id) }}">
                @csrf
                @foreach ($questions as $index => $question)
                    <div class="border rounded p-4 mb-4 question-card" data-question-id="{{ $question['id'] }}">
                        <h5>{{ $index + 1 }}. {!! $question['question_text'] !!} <small>({{ $question['marks'] }})</small></h5>
                        @if ($question['image'])
                            <img src="{{ $question['image'] }}" alt="" class="img-fluid mb-3" style="max-height:180px">
                        @endif
                        @php $saved = collect($attempt->answers ?? [])->firstWhere('question_id', $question['id']); $selected = $saved['selected_options'] ?? $saved['answer'] ?? []; @endphp
                        @if (in_array($question['type'], ['multiple_choice', 'true_false']))
                            @foreach ($question['options'] as $optionKey => $option)
                                <label class="d-block mb-2">
                                    <input type="radio" name="answers[{{ $index }}][selected_options][]" value="{{ is_int($optionKey) ? $option : $optionKey }}"
                                        @checked(in_array((string) (is_int($optionKey) ? $option : $optionKey), array_map('strval', (array) $selected), true))>
                                    {{ $option }}
                                </label>
                            @endforeach
                        @elseif ($question['type'] === 'multiple_select')
                            @foreach ($question['options'] as $optionKey => $option)
                                <label class="d-block mb-2">
                                    <input type="checkbox" name="answers[{{ $index }}][selected_options][]" value="{{ is_int($optionKey) ? $option : $optionKey }}"
                                        @checked(in_array((string) (is_int($optionKey) ? $option : $optionKey), array_map('strval', (array) $selected), true))>
                                    {{ $option }}
                                </label>
                            @endforeach
                        @else
                            <textarea class="form-control" name="answers[{{ $index }}][answer]">{{ is_array($selected) ? implode(' ', $selected) : $selected }}</textarea>
                        @endif
                        <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question['id'] }}">
                    </div>
                @endforeach
                <button class="btn btn-primary" type="submit">{{ _lang('Submit Exam') }}</button>
            </form>
        </div>
    </div>
@endsection
@section('js-script')
    <script>
        (function() {
            const expiresAt = new Date(@json(optional($attempt->expires_at)->toIso8601String()));
            const form = document.getElementById('exam-form');
            const timerEl = document.getElementById('exam-timer');
            const saveUrl = @json(route('student.online-exams.save', $attempt->id));
            const token = document.querySelector('input[name=_token]').value;

            function remaining() {
                const diff = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
                const m = String(Math.floor(diff / 60)).padStart(2, '0');
                const s = String(diff % 60).padStart(2, '0');
                timerEl.textContent = m + ':' + s;
                if (diff <= 0) {
                    form.submit();
                }
            }
            remaining();
            setInterval(remaining, 1000);

            function collect() {
                return new FormData(form);
            }
            async function autosave() {
                const body = new URLSearchParams(collect());
                body.append('_token', token);
                try {
                    await fetch(saveUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
                } catch (e) {}
            }
            setInterval(autosave, 15000);
            window.addEventListener('beforeunload', function() { navigator.sendBeacon(saveUrl, collect()); });
        })();
    </script>

    @if ($attempt->onlineExam->proctoring_enabled)
        <script>
            (function() {
                const violationUrl = @json(route('student.online-exams.violation', $attempt->id));
                const token = document.querySelector('input[name=_token]').value;
                const examCard = document.getElementById('examCard');
                const fullscreenBtn = document.getElementById('enterFullscreenBtn');
                let submitted = false;

                async function reportViolation(type, detail) {
                    if (submitted) return;
                    try {
                        const res = await fetch(violationUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({ _token: token, type, detail: detail || '' }),
                        });
                        const data = await res.json();
                        if (data.auto_submitted) {
                            submitted = true;
                            alert(@json(_lang('This exam has been automatically submitted after repeated integrity violations.')));
                            window.location.href = @json(route('student.online-exams.result', $attempt->id));
                        }
                    } catch (e) {}
                }

                // ---------- Fullscreen ----------
                function requestFullscreen() {
                    const el = document.documentElement;
                    if (el.requestFullscreen) el.requestFullscreen().catch(() => {});
                }
                fullscreenBtn?.addEventListener('click', requestFullscreen);

                document.addEventListener('fullscreenchange', () => {
                    if (!document.fullscreenElement) {
                        reportViolation('exited_fullscreen');
                    }
                });

                // ---------- Tab switch / window blur ----------
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        reportViolation('tab_hidden');
                    }
                });
                window.addEventListener('blur', () => reportViolation('window_blur'));

                // ---------- Copy / cut / paste / right-click blocked on the exam itself ----------
                ['copy', 'cut', 'paste', 'contextmenu'].forEach((evt) => {
                    examCard.addEventListener(evt, (e) => {
                        e.preventDefault();
                        reportViolation(evt);
                    });
                });

                // Discourage text selection on question content (deterrent only —
                // does not and cannot prevent screenshots or a second device).
                examCard.style.userSelect = 'none';
            })();
        </script>
    @endif
@stop
