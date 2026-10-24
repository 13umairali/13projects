<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4f46e5">

    {{-- Set theme before paint to avoid a flash of the wrong color scheme. --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (!t) {
                    t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    @includeFirst(['partials.seo'])

    @if (!empty($brand['favicon']))
        <link rel="icon" href="{{ $brand['favicon'] }}">
    @endif

    @include('partials.vite-assets')

    @stack('head')
</head>

<body class="min-h-screen bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100">

    <a href="#main"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    <x-landing.navbar :brand="$brand" :nav="$nav" :authed="$authed ?? false" />

    <main id="main">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-landing.footer :brand="$brand" :nav="$nav" :socials="$socials ?? []" />

    @stack('scripts')


<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6aad842933605f344884cc14/1k2qsogpe';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>


<script>
(function () {
  var CSS_RULES = `
    /* "Powered by tawk.to" branding */
    a[style*="border-radius: 100px"][style*="font-size: 12px"],
    a[style*="font-size: 12px"][style*="font-weight: 400"][style*="padding"],

    /* "Add Chat to your website" menu item */
    a[href*="utm_source=tawk-messenger"],

    /* "Pop out widget" menu item */
    button:has(i.tawk-icon-popout),

    /* "Email transcript" menu item */
    button:has(i.tawk-icon-envelope) {
      display: none !important;
    }
  `;

  function injectStyle(doc) {
    try {
      if (!doc || doc.getElementById('hide-tawk-badge')) return;
      var style = doc.createElement('style');
      style.id = 'hide-tawk-badge';
      style.textContent = CSS_RULES;
      (doc.head || doc.documentElement).appendChild(style);
    } catch (e) {}
  }

  function textFallback(doc) {
    try {
      doc.querySelectorAll('a, button').forEach(function (el) {
        var t = (el.textContent || '').toLowerCase().trim();
        var hasLogoSvg = el.querySelector && el.querySelector('svg[viewBox="0 0 107 15"]');
        var isAddChat = t.includes('add chat to your website');
        var isPopout = t.includes('pop out widget');
        var isEmailTranscript = t.includes('email transcript');
        if (hasLogoSvg || isAddChat || isPopout || isEmailTranscript) {
          el.style.setProperty('display', 'none', 'important');
        }
      });
    } catch (e) {}
  }

  function process(doc) {
    injectStyle(doc);
    textFallback(doc);
  }

  process(document);

  function handleIframe(iframe) {
    var tryInject = function () {
      try {
        var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
        process(doc);
      } catch (e) {}
    };
    tryInject();
    iframe.addEventListener('load', tryInject);
  }

  document.querySelectorAll('iframe').forEach(handleIframe);

  new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeName === 'IFRAME') handleIframe(node);
        else if (node.querySelectorAll) node.querySelectorAll('iframe').forEach(handleIframe);
      });
    });
  }).observe(document.documentElement, { childList: true, subtree: true });

  var tick = setInterval(function () {
    process(document);
    document.querySelectorAll('iframe').forEach(function (f) {
      try {
        process(f.contentDocument || (f.contentWindow && f.contentWindow.document));
      } catch (e) {}
    });
  }, 100);
  setTimeout(function () { clearInterval(tick); }, 8000);
})();
</script>



<!--End of Tawk.to Script-->




</body>

</html>
