<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- Reset links carry a token in the URL; never leak it to third-party assets via Referer. --}}
<meta name="referrer" content="no-referrer">
<title>@hasSection('title')@yield('title') · @endif IVEA Analítica</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..24,400,0..1,0&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
