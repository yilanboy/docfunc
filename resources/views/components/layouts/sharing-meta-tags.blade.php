@props(['title'])

@php
  $defaultPreviewUrl = 'https://blobs.docfunc.com/share.jpg';
@endphp

{{-- Open Graph / Facebook --}}
<meta
  property="og:url"
  content="{{ url()->full() }}"
>
<meta
  property="og:type"
  content="{{ request()->route()->getName() === 'posts.show'? 'article': 'website' }}"
>
<meta
  property="og:title"
  content="{{ $title }}"
>
<meta
  property="og:description"
  content="@yield('description', config('app.name'))"
>
<meta
  property="og:image"
  content="@yield('preview_url', $defaultPreviewUrl)"
>

{{-- Twitter --}}
<meta
  property="twitter:card"
  content="summary_large_image"
>
<meta
  property="twitter:url"
  content="{{ url()->full() }}"
>
<meta
  property="twitter:title"
  content="{{ $title }}"
>
<meta
  property="twitter:description"
  content="@yield('description', config('app.name'))"
>
<meta
  property="twitter:image"
  content="@yield('preview_url', $defaultPreviewUrl)"
>
