<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
        <xhtml:link rel="alternate" hreflang="cs" href="{{ url('/lang/cs') }}"/>
        <xhtml:link rel="alternate" hreflang="en" href="{{ url('/lang/en') }}"/>
    </url>
    <url>
        <loc>{{ url('/o-nas') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    @foreach($events as $event)
    <url>
        <loc>{{ url('/udalosti/' . $event->slug) }}</loc>
        <lastmod>{{ $event->updated_at->format('Y-m-d') }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>
