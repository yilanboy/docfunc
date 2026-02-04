<!DOCTYPE html>
<html
    class="scroll-smooth"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Primary Meta Tags --}}
    <meta name="title" content="{{ $title ?? config('app.name') }}">
    <meta name="description" content="@yield('description', config('app.name'))">
    <x-layouts.sharing-meta-tags :title="$title ?? config('app.name')" />

    {{-- Ｗeb Feed --}}
    @include('feed::links')

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('images/icon/logo.svg') }}" type="image/png">

    @vite(['resources/ts/app.ts','resources/css/app.css'])

    {{-- Cloudflare Turnstile --}}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
</head>

<body class="overscroll-y-none relative text-lg antialiased font-noto-sans bg-zinc-200 text-zinc-900 dark:bg-zinc-900">
{{-- Set theme --}}
<script>
    if (
        localStorage.theme === 'light' ||
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)
    ) {
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
</script>

<x-layouts.background />

{{ $slot }}

@persist('toast')
<x-toast />
@endpersist
</body>

</html>
