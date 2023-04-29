@php
  $defaultPreviewUrl = 'https://' . config('filesystems.disks.s3.bucket') . '.s3.' . config('filesystems.disks.s3.region') . '.amazonaws.com/share.jpg';
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
  content="@yield('title', config('app.name'))"
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
  content="@yield('title', config('app.name'))"
>
<meta
  property="twitter:description"
  content="@yield('description', config('app.name'))"
>
<meta
  property="twitter:image"
  content="@yield('preview_url', $defaultPreviewUrl)"
>
