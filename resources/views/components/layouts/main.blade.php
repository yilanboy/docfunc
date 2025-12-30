<div
    class="relative selection:bg-emerald-300 selection:text-emerald-900 dark:selection:bg-indigo-300 dark:selection:text-indigo-900"
    {{ $attributes }}
>
    <x-layouts.background />

    <div class="flex flex-col min-h-screen">
        <livewire:layouts.header />

        {{ $slot }}
    </div>

    <x-layouts.footer />
</div>
