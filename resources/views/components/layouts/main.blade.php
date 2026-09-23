<div
    class="selection:bg-emerald-300 selection:text-emerald-900 dark:selection:bg-indigo-300 dark:selection:text-indigo-900"
    {{ $attributes }}
>
    <div class="flex min-h-screen flex-col">
        <livewire:layouts.header />

        <main wire:transition.navigate class="flex grow flex-col">
            {{ $slot }}
        </main>
    </div>

    <x-layouts.footer />
</div>
