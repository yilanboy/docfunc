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
@vite([
    'resources/ts/tagify.ts',
    'node_modules/@yaireo/tagify/dist/tagify.css',
    'resources/css/custom-tagify.css',
])
@endassets

@script
<script>
    Alpine.data('tagifyComponent', () => ({
        tagsListUrl: @js(route('api.tags')),
        async init() {
            const response = await fetch(this.tagsListUrl);
            const tagsList = await response.json();

            const tagify = window.createTagify(
                this.$refs.tags,
                tagsList.data
            );

            try {
                const tags = JSON.parse(this.$wire.value);
                tagify.addTags(tags);
            } catch (error) {
                console.error('Error parsing tags:', error);
            }

            tagify.on('change', (event) => {
                this.$wire.value = event.detail.value;
            });

            document.addEventListener('livewire:navigating', () => {
                tagify.destroy();
            }, { once: true });
        }
    }));
</script>
@endscript

<div
    x-data="tagifyComponent"
    wire:ignore
>
    <label
        class="hidden"
        for="tags"
    >標籤 (最多 5 個)</label>

    <input
        class="tagify-custom-look dark:border-zinc-600! border-zinc-300! w-full rounded-md bg-white dark:bg-zinc-700"
        id="tags"
        type="text"
        placeholder="標籤 (最多 5 個)"
        x-ref="tags"
    >
</div>
