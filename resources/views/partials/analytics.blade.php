{{--
    Google Analytics 4 — Google Consent Mode v2 (GDPR / ePrivacy).
    gtag.js se vloží POUZE pokud je nastaveno config('services.google_analytics.id').

    Consent Mode v2:
      - default = vše 'denied' (ad_*, analytics_storage) → GA jede COOKIELESS:
        posílá anonymní pingy bez identifikátorů/cookies → modelovaná data i bez souhlasu.
      - po udělení souhlasu (cookie cookie_consent.analytics === true) se zavolá
        gtag('consent','update',{analytics_storage:'granted'}) → plný režim s cookies.
      - při odebrání souhlasu zpět na 'denied' (bez reloadu, viz cookie-consent.blade.php).
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

            window.dataLayer = window.dataLayer || [];
            window.gtag = function () { window.dataLayer.push(arguments); };

            // 1) Default consent — vše denied (cookieless režim) PŘED config.
            window.gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                wait_for_update: 500
            });

            // 2) Pokud už dal souhlas dříve → rovnou granted (cookies povoleny).
            if (hasAnalyticsConsent()) {
                window.gtag('consent', 'update', { analytics_storage: 'granted' });
            }

            // 3) Načti gtag.js + config (běží i v denied = cookieless pingy).
            window.gtag('js', new Date());
            window.gtag('config', GA_ID, { anonymize_ip: true });

            var s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GA_ID);
            document.head.appendChild(s);

            // 4) Změna souhlasu z cookie lišty (grant i revoke) bez reloadu.
            window.zdaraiConsentUpdate = function (granted) {
                if (typeof window.gtag !== 'function') return;
                window.gtag('consent', 'update', {
                    analytics_storage: granted ? 'granted' : 'denied'
                });
            };
        })();
    </script>
@endif
