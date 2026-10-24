{{--
    First-run installer.

    Deliberately standalone: no @vite, no layout inheritance, no shared $brand.
    This page has to render on a fresh clone before `npm run build` has ever
    run and while no backend is reachable — the two conditions under which the
    normal layouts cannot render at all. Everything it needs is inline.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Setup · {{ config('saas.brand.name') }}</title>
    <link rel="icon"
        href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22><text y=%22.9em%22 font-size=%2290%22>⚙️</text></svg>">
    <style>
        :root {
            --bg: #070a13;
            --card: #0f1524;
            --card-soft: #151c2e;
            --border: #232c42;
            --text: #e8ecf7;
            --muted: #8b97b3;
            --brand: {{ config('saas.brand.primary', '#6366f1') }};
            --accent: {{ config('saas.brand.accent', '#8b5cf6') }};
            --ok: #34d399;
            --err: #f87171;
            --radius: 16px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--bg);
            color: var(--text);
            font: 15px/1.6 "Instrument Sans", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Ambient glow. Fixed + non-interactive so it never eats a click. */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(60vw 60vw at 15% 0%, color-mix(in srgb, var(--brand) 22%, transparent), transparent 60%),
                radial-gradient(50vw 50vw at 85% 100%, color-mix(in srgb, var(--accent) 18%, transparent), transparent 55%);
        }

        .card {
            position: relative;
            width: 100%;
            max-width: 560px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: 0 24px 60px -12px rgb(0 0 0 / .7);
            overflow: hidden;
        }

        .card__head {
            padding: 32px 32px 24px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(160deg, color-mix(in srgb, var(--brand) 12%, transparent), transparent 70%);
        }

        .brand { display: flex; align-items: center; gap: 12px; }

        .brand__mark {
            display: grid;
            place-items: center;
            width: 44px; height: 44px;
            flex: none;
            border-radius: 12px;
            color: #fff;
            background: linear-gradient(135deg, var(--brand), var(--accent));
        }

        .brand__name { font-size: 17px; font-weight: 600; letter-spacing: -.01em; }
        .brand__sub { font-size: 13px; color: var(--muted); }

        h1 { margin: 24px 0 8px; font-size: 24px; font-weight: 600; letter-spacing: -.02em; }
        .lede { margin: 0; color: var(--muted); font-size: 14px; }

        /* Steps */
        .steps { display: flex; gap: 8px; padding: 20px 32px; border-bottom: 1px solid var(--border); background: var(--card-soft); }
        .step { flex: 1; display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--muted); min-width: 0; }
        .step__dot {
            display: grid; place-items: center;
            width: 22px; height: 22px; flex: none;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: var(--card);
            font-size: 11px; font-weight: 600;
            transition: all .3s ease;
        }
        .step__label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .step[data-state="active"] { color: var(--text); }
        .step[data-state="active"] .step__dot { border-color: var(--brand); background: var(--brand); color: #fff; }
        .step[data-state="done"] { color: var(--ok); }
        .step[data-state="done"] .step__dot { border-color: var(--ok); background: color-mix(in srgb, var(--ok) 18%, transparent); color: var(--ok); }

        .card__body { padding: 32px; }

        label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; }

        .field { display: flex; gap: 8px; }
        @media (max-width: 480px) { .field { flex-direction: column; } }

        input[type="url"] {
            flex: 1;
            min-width: 0;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            font: inherit;
            font-size: 14px;
            transition: border-color .15s, box-shadow .15s;
        }
        input[type="url"]::placeholder { color: #55607d; }
        input[type="url"]:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 25%, transparent);
        }
        input[type="url"][aria-invalid="true"] { border-color: var(--err); }

        .hint { margin: 8px 0 0; font-size: 12px; color: var(--muted); }

        button {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 18px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--card-soft);
            color: var(--text);
            font: inherit; font-size: 14px; font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: transform .12s, filter .15s, opacity .15s;
        }
        button:hover:not(:disabled) { filter: brightness(1.25); }
        button:active:not(:disabled) { transform: translateY(1px); }
        button:disabled { opacity: .5; cursor: not-allowed; }
        button:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }

        .btn--primary {
            width: 100%;
            margin-top: 20px;
            border: 0;
            padding: 13px 18px;
            background: linear-gradient(135deg, var(--brand), var(--accent));
            color: #fff;
            font-weight: 600;
        }

        /* Feedback */
        .alert {
            display: none;
            gap: 10px;
            margin-top: 20px;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid;
            font-size: 13px;
            animation: rise .25s ease-out;
        }
        .alert[data-show="true"] { display: flex; }
        .alert--ok { border-color: color-mix(in srgb, var(--ok) 40%, transparent); background: color-mix(in srgb, var(--ok) 10%, transparent); }
        .alert--err { border-color: color-mix(in srgb, var(--err) 40%, transparent); background: color-mix(in srgb, var(--err) 10%, transparent); }
        .alert__icon { flex: none; margin-top: 1px; }
        .alert--ok .alert__icon { color: var(--ok); }
        .alert--err .alert__icon { color: var(--err); }
        .alert__title { font-weight: 600; }
        .alert__hint { margin-top: 4px; color: var(--muted); overflow-wrap: anywhere; }

        @keyframes rise { from { opacity: 0; transform: translateY(6px); } }

        .spinner {
            width: 15px; height: 15px; flex: none;
            border: 2px solid rgb(255 255 255 / .3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Success state */
        .done { display: none; padding: 48px 32px; text-align: center; }
        .done[data-show="true"] { display: block; animation: rise .3s ease-out; }
        .done__ring {
            width: 72px; height: 72px;
            margin: 0 auto 20px;
            display: grid; place-items: center;
            border-radius: 50%;
            color: var(--ok);
            background: color-mix(in srgb, var(--ok) 12%, transparent);
            border: 1px solid color-mix(in srgb, var(--ok) 35%, transparent);
            animation: pop .4s cubic-bezier(.34, 1.56, .64, 1);
        }
        @keyframes pop { 0% { transform: scale(.6); opacity: 0; } }
        .done__check { stroke-dasharray: 32; stroke-dashoffset: 32; animation: draw .4s .2s ease-out forwards; }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        .done h2 { margin: 0 0 8px; font-size: 20px; font-weight: 600; }
        .done p { margin: 0; color: var(--muted); font-size: 14px; }

        .foot { padding: 16px 32px; border-top: 1px solid var(--border); background: var(--card-soft); color: var(--muted); font-size: 12px; text-align: center; }
        code { padding: 1px 5px; border-radius: 4px; background: var(--bg); font-size: 12px; }

        .sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
        }
    </style>
</head>

<body>
    <main class="card">
        <div class="card__head">
            <div class="brand">
                <span class="brand__mark" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10L12 5 2 10l10 5 10-5z" />
                        <path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5" />
                    </svg>
                </span>
                <span>
                    <span class="brand__name">{{ config('saas.brand.name') }}</span><br>
                    <span class="brand__sub">Installation</span>
                </span>
            </div>

            <h1>Connect your backend</h1>
            <p class="lede">
                This panel reads everything from your {{ config('saas.brand.name') }} backend API.
                Point it at that server to finish setting up.
            </p>
        </div>

        <ol class="steps" id="steps">
            <li class="step" data-step="1" data-state="active">
                <span class="step__dot">1</span><span class="step__label">Enter URL</span>
            </li>
            <li class="step" data-step="2">
                <span class="step__dot">2</span><span class="step__label">Test</span>
            </li>
            <li class="step" data-step="3">
                <span class="step__dot">3</span><span class="step__label">Save</span>
            </li>
            <li class="step" data-step="4">
                <span class="step__dot">4</span><span class="step__label">Done</span>
            </li>
        </ol>

        {{-- Form. Progressive enhancement: without JS this posts to /setup/save
             normally; the handler answers JSON, so JS is the intended path. --}}
        <form class="card__body" id="form" action="{{ route('setup.save') }}" method="POST" novalidate>
            @csrf

            <label for="url">Backend API URL</label>
            <div class="field">
                <input type="url" id="url" name="url" placeholder="http://127.0.0.1:8000"
                    value="{{ old('url', $currentUrl) }}" autocomplete="off" autocapitalize="off"
                    spellcheck="false" autofocus required
                    aria-describedby="hint">
                <button type="button" id="test">Test connection</button>
            </div>
            <p class="hint" id="hint">
                The server's root address — no trailing slash and no <code>/api</code>.
            </p>

            <div class="alert" id="alert" role="status" aria-live="polite">
                <span class="alert__icon" id="alert-icon" aria-hidden="true"></span>
                <span>
                    <span class="alert__title" id="alert-title"></span>
                    <span class="alert__hint" id="alert-hint"></span>
                </span>
            </div>

            <button type="submit" class="btn--primary" id="save" disabled>
                Save configuration
            </button>
        </form>

        {{-- Swapped in for the form on success. --}}
        <div class="done" id="done">
            <div class="done__ring">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path class="done__check" d="M20 6L9 17l-5-5" />
                </svg>
            </div>
            <h2>Configuration saved</h2>
            <p id="done-note">Taking you to the sign-in page…</p>
        </div>

        <p class="foot">
            Saved to your <code>.env</code> file. This page locks itself once setup is complete.
        </p>
    </main>

    <script>
        (function () {
            'use strict';

            var form = document.getElementById('form');
            var input = document.getElementById('url');
            var testBtn = document.getElementById('test');
            var saveBtn = document.getElementById('save');
            var alertBox = document.getElementById('alert');
            var alertIcon = document.getElementById('alert-icon');
            var alertTitle = document.getElementById('alert-title');
            var alertHint = document.getElementById('alert-hint');
            var doneBox = document.getElementById('done');
            var doneNote = document.getElementById('done-note');
            var steps = document.getElementById('steps');

            var routes = {
                test: @json(route('setup.test')),
                save: @json(route('setup.save'))
            };
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var ICON_OK = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
            var ICON_ERR = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>';

            // The URL proven reachable by the last successful test. Save unlocks
            // only while the field still matches it, so an edited-but-untested
            // URL can never be submitted.
            var verified = null;

            function setStep(n, state) {
                var el = steps.querySelector('[data-step="' + n + '"]');
                if (!el) return;
                if (state) el.dataset.state = state;
                else delete el.dataset.state;
            }

            function progress(active) {
                [1, 2, 3, 4].forEach(function (n) {
                    setStep(n, n < active ? 'done' : (n === active ? 'active' : null));
                });
            }

            function feedback(ok, title, hint) {
                alertBox.dataset.show = 'true';
                alertBox.className = 'alert alert--' + (ok ? 'ok' : 'err');
                alertIcon.innerHTML = ok ? ICON_OK : ICON_ERR;
                alertTitle.textContent = title || '';
                alertHint.textContent = hint || '';
                input.setAttribute('aria-invalid', ok ? 'false' : 'true');
            }

            function clearFeedback() {
                alertBox.dataset.show = 'false';
                input.removeAttribute('aria-invalid');
            }

            function busy(btn, on, label) {
                btn.disabled = on;
                if (on) {
                    btn.dataset.label = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner"></span>' + label;
                } else if (btn.dataset.label) {
                    btn.innerHTML = btn.dataset.label;
                }
            }

            function post(url) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ url: input.value })
                }).then(function (res) {
                    return res.json()
                        .catch(function () {
                            // A non-JSON body means something upstream (proxy,
                            // PHP fatal) answered instead of the app.
                            return { message: 'The server returned an unreadable response (HTTP ' + res.status + ').' };
                        })
                        .then(function (body) {
                            // 419: the CSRF token died with the session. Telling
                            // them to reload beats a silent, repeatable failure.
                            if (res.status === 419) {
                                body.message = 'Your session expired.';
                                body.hint = 'Reload the page and try again.';
                            }
                            if (res.status === 429) {
                                body.message = 'Too many attempts.';
                                body.hint = 'Wait a minute before trying again.';
                            }
                            // Laravel validation errors arrive under `errors`.
                            if (res.status === 422 && body.errors && body.errors.url) {
                                body.message = body.errors.url[0];
                                body.hint = body.hint || '';
                            }
                            return { ok: res.ok && body.ok !== false, body: body };
                        });
                }).catch(function () {
                    return {
                        ok: false,
                        body: {
                            message: 'Could not reach this panel.',
                            hint: 'Check that the web server is still running, then retry.'
                        }
                    };
                });
            }

            function lock() {
                verified = null;
                saveBtn.disabled = true;
            }

            input.addEventListener('input', function () {
                // Any edit invalidates the previous test result.
                if (verified !== null && input.value.trim().replace(/\/+$/, '') !== verified) {
                    lock();
                    clearFeedback();
                    progress(1);
                }
            });

            testBtn.addEventListener('click', function () {
                if (!input.value.trim()) {
                    feedback(false, 'Enter the address of your backend API.', '');
                    input.focus();
                    return;
                }

                clearFeedback();
                lock();
                progress(2);
                busy(testBtn, true, 'Testing…');

                post(routes.test).then(function (r) {
                    busy(testBtn, false);

                    if (r.ok) {
                        verified = input.value.trim().replace(/\/+$/, '');
                        saveBtn.disabled = false;
                        progress(3);
                        feedback(true, r.body.message || 'Connection successful.',
                            r.body.latency_ms ? 'Responded in ' + r.body.latency_ms + ' ms.' : '');
                        saveBtn.focus();
                    } else {
                        progress(1);
                        feedback(false, r.body.message || 'Connection failed.', r.body.hint || '');
                    }
                });
            });

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                if (saveBtn.disabled) return;

                busy(saveBtn, true, 'Saving…');
                progress(3);

                post(routes.save).then(function (r) {
                    if (!r.ok) {
                        busy(saveBtn, false);
                        lock();
                        progress(1);
                        feedback(false, r.body.message || 'Could not save.', r.body.hint || '');
                        return;
                    }

                    progress(4);
                    setStep(4, 'done');
                    form.style.display = 'none';
                    doneBox.dataset.show = 'true';

                    var target = r.body.redirect || '/';
                    doneNote.textContent = 'Taking you to the sign-in page…';
                    setTimeout(function () { window.location.href = target; }, 1400);
                });
            });
        })();
    </script>
</body>

</html>
