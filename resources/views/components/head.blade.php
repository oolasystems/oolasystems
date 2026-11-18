<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0A0A0A">
<meta name="robots" content="index,follow,max-image-preview:large">
<meta name="author" content="Oola Systems">
<meta name="keywords"
    content="software engineering, technology consulting, AI integration, cloud engineering, web development, enterprise software, Dubai, UAE">

<title>@yield('title', 'Oola Systems | Enterprise Software Engineering & Technology Consulting')</title>
<meta name="description" content="@yield('meta_description', 'Oola Systems is an enterprise software engineering and technology consulting partner with 25+ years of experience, serving startups, SMEs, enterprises, and government across the UAE, Saudi Arabia, Qatar, Oman, Bahrain, and Kuwait.')">
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Oola Systems">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="@yield('og_title', 'Oola Systems | Enterprise Software Engineering & Technology Consulting')">
<meta property="og:description" content="@yield('og_description', 'Enterprise software engineering and technology consulting for the Gulf region.')">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('images/os-logo.png') }}">
<meta name="twitter:card" content="summary_large_image">

{{-- Favicons — Google Search requires a crawlable icon that is a multiple of 48px (e.g. 48x48) --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('images/favicon-48x48.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icon-192x192.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('images/site.webmanifest') }}">

{{-- Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap"
    rel="stylesheet">

{{-- Bootstrap 5 (CSS only) --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

{{-- Oola Systems design system (v1.0) --}}
<link href="{{ asset('css/style.css') }}?v=1.0" rel="stylesheet">

@stack('head')
