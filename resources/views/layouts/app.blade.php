<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ID Watch') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-gray-800">
    {{-- Ini adalah tempat konten spesifik halaman akan diinject --}}
    {{-- {{ $slot }} Untuk Livewire Page Components dan Blade Components --}}
    @yield('content') {{-- Untuk Blade Views yang menggunakan @extends & @section --}}

    @livewireScripts
</body>
</html>