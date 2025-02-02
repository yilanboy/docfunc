<!DOCTYPE html>
<html
  class="scroll-smooth"
  lang="{{ str_replace('_', '-', app()->getLocale()) }}"
>

<head>
  {{-- prettier-ignore-start --}}
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- CSRF Token --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ $title ?? config('app.name') }}</title>

  {{-- Primary Meta Tags --}}
  <meta name="title" content="{{ $title ?? config('app.name') }}">
  <meta name="description" content="@yield('description', config('app.name'))">
  <x-sharing-meta-tags :title="$title ?? config('app.name')" />

  {{-- Ｗeb Feed --}}
  @include('feed::links')

  {{-- Favicon --}}
  <link rel="icon" href="{{ asset('images/icon/icon.png') }}" type="image/png">

  {{-- Font --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Noto+Sans+TC:wght@100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
    rel="stylesheet">

  @vite('resources/css/app.css')

  {{-- Cloudflare Turnstile --}}
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
  {{-- prettier-ignore-end --}}
</head>

<body class="font-noto-sans overscroll-y-none bg-gray-200 text-lg text-gray-900 antialiased dark:bg-gray-900">
  {{-- Set theme --}}
  <script>
    if (
      localStorage.theme === 'light' ||
      (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)
    ) {
      document.documentElement.classList.remove('dark');
    } else {
      document.documentElement.classList.add('dark');
    }
  </script>

  {{ $slot }}

  {{-- Flash Alert --}}
  @persist('alert')
    <x-alert />
  @endpersist
</body>

</html>
