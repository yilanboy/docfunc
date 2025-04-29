<div
  {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-emerald-500 bg-zinc-50 p-5 dark:border-indigo-600 dark:bg-zinc-800']) }}
>
  {{ $slot }}
</div>
