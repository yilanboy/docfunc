@props(['errors'])

@if ($errors->any())
  <div {{ $attributes }}>
    <div class="font-medium text-red-400">
      {{ __('Whoops! Something went wrong.') }}
    </div>

    <ul class="mt-3 text-red-400 list-disc list-inside">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
