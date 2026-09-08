<?php

declare(strict_types=1);

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public string $value = '';
};
?>

@assets
    @vite('resources/ts/tagify.ts')
@endassets

<script>
    Alpine.data('tagifyComponent', () => ({
        async init() {
            const tagsListUrl = this.$el.dataset.tagsListUrl;
            const response = await fetch(tagsListUrl);
            const tagsList = await response.json();

            const tagify = window.createTagify(this.$refs.tags, tagsList);

            try {
                const tags = JSON.parse(this.$wire.value);
                tagify.addTags(tags);
            } catch (error) {
                console.error('Error parsing tags:', error);
            }

            // Prevent from triggering when component is initialized
            setTimeout(() => {
                tagify.on('change', (event) => {
                    this.$wire.value = event.detail.value;
                });
            }, 500);

            document.addEventListener(
                'livewire:navigating',
                () => {
                    tagify.destroy();
                },
                { once: true },
            );

            this.$dispatch('tagify-ready');
        },
    }));
</script>

<div
    data-tags-list-url="{{ route('api.tags') }}"
    wire:ignore
    x-data="tagifyComponent"
>
    <label class="hidden" for="tags">標籤 (最多 5 個)</label>

    <input
        class="tagify-custom-look inline-flex w-full items-center rounded-md border-zinc-300! bg-white dark:border-zinc-600! dark:bg-zinc-700"
        id="tags"
        type="text"
        placeholder="標籤 (最多 5 個)"
        x-ref="tags"
    />
</div>
