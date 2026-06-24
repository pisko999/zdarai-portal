{{--
    Google Analytics 4 — consent-gated (GDPR / ePrivacy).
    gtag.js se vloží POUZE pokud:
      (a) je nastaveno config('services.google_analytics.id'), A
      (b) uživatel udělil souhlas s kategorií 'analytics' (cookie cookie_consent).
    Funguje i bez reloadu: po "Přijmout vše" volá CookieConsent zde definované
    window.zdaraiLoadAnalytics() (viz cookie-consent.blade.php Livewire listener).
--}}
@php($gaId = config('services.google_analytics.id'))
@if($gaId)
    <script @cspNonce>
        (function () {
            var GA_ID = @json($gaId);

            function hasAnalyticsConsent() {
                try {
                    var match = document.cookie.match(/(?:^|;\s*)cookie_consent=([^;]*)/);
                    if (!match) return false;
                    var data = JSON.parse(decodeURIComponent(match[1]));
                    return data && data.analytics === true;
                } catch (e) {
                    return false;
                }
            }

            window.zdaraiLoadAnalytics = function () {
                if (window.__zdaraiGaLoaded) return;
                window.__zdaraiGaLoaded = true;

                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GA_ID);
                document.head.appendChild(s);

                window.dataLayer = window.dataLayer || [];
                window.gtag = function () { window.dataLayer.push(arguments); };
                window.gtag('js', new Date());
                window.gtag('config', GA_ID, { anonymize_ip: true });
            };

            // Souhlas už udělen v předchozí návštěvě → načti hned.
            if (hasAnalyticsConsent()) {
                window.zdaraiLoadAnalytics();
            }
        })();
    </script>
@endif
