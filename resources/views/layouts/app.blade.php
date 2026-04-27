<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title ?? 'PKL SMKN 7 Pontianak')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>

    @livewireStyles
</head>

<body class="min-h-screen bg-linear-135 from-slate-50 via-cyan-50 to-emerald-50 text-slate-800 antialiased">
    @hasSection('content')
        @yield('content')
    @else
        {{ $slot ?? '' }}
    @endif

    @livewireScripts
</body>

</html>
