<?php

declare(strict_types=1);

use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public ?string $imageUrl = null;
};
?>

@script
    <script>
        Alpine.data('uploadCoverImage', () => ({
            imageUrl: $wire.entangle('imageUrl'),
            uploading: false,
            errorMessage: null,
            changeBlockStyleWhenDragEnter() {
                this.$refs.uploadBlock.classList.remove(
                    'text-emerald-500',
                    'dark:text-indigo-400',
                    'border-emerald-500',
                    'dark:border-indigo-400',
                );
                this.$refs.uploadBlock.classList.add(
                    'text-emerald-600',
                    'dark:text-indigo-300',
                    'border-emerald-600',
                    'dark:border-indigo-300',
                );
            },
            changeBlockStyleWhenDragLeaveAndDrop() {
                this.$refs.uploadBlock.classList.add(
                    'text-emerald-500',
                    'dark:text-indigo-400',
                    'border-emerald-500',
                    'dark:border-indigo-400',
                );
                this.$refs.uploadBlock.classList.remove(
                    'text-emerald-600',
                    'dark:text-indigo-300',
                    'border-emerald-600',
                    'dark:border-indigo-300',
                );
            },
            removeCoverImage() {
                if (confirm('你確定要刪除封面圖嗎？')) {
                    this.imageUrl = null;
                }
            },
            async uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.uploading = true;
                this.errorMessage = null;

                const formData = new FormData();
                formData.append('upload', file);

                try {
                    const response = await fetch('{{ route('images.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            Accept: 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.imageUrl = result.url;
                    } else {
                        this.errorMessage = result.error?.message || '上傳失敗，請稍後再試';
                    }
                } catch (error) {
                    this.errorMessage = '上傳過程發生錯誤';
                } finally {
                    this.uploading = false;
                    event.target.value = '';
                }
            },
        }));
    </script>
@endscript

<div class="col-span-2 text-base" x-data="uploadCoverImage">
    {{-- image preview --}}
    <div class="relative w-full" x-cloak x-show="imageUrl !== null">
        <img class="rounded-lg" id="image-url" x-bind:src="imageUrl" alt="image url" />

        <button
            class="group absolute top-0 right-0 flex h-full w-full cursor-pointer items-center justify-center rounded-lg transition-all duration-150 hover:bg-zinc-600/50 hover:backdrop-blur-xs"
            type="button"
            x-on:click="removeCoverImage"
        >
            <x-icons.x-circle class="size-24 opacity-0 transition-all duration-150 group-hover:text-zinc-50 group-hover:opacity-100" />
        </button>

        <span class="absolute top-2 right-2 inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-sm font-medium text-emerald-700 ring-1 ring-emerald-700/10 ring-inset dark:bg-gray-50 dark:text-gray-700 dark:ring-gray-700/10">封面圖</span>
    </div>

    {{-- Upload Area --}}
    <div
        class="relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-dashed border-emerald-500 bg-transparent px-4 py-6 tracking-wide text-emerald-500 transition-all duration-300 hover:border-emerald-600 hover:text-emerald-600 dark:border-indigo-400 dark:text-indigo-400 dark:hover:border-indigo-300 dark:hover:text-indigo-300"
        x-ref="uploadBlock"
        x-cloak
        x-show="imageUrl === null"
    >
        <input
            class="absolute inset-0 z-50 m-0 h-full w-full cursor-pointer p-0 opacity-0 outline-hidden"
            type="file"
            x-on:change="uploadImage"
            x-on:dragenter="changeBlockStyleWhenDragEnter"
            x-on:dragleave="changeBlockStyleWhenDragLeaveAndDrop"
            x-on:drop="changeBlockStyleWhenDragLeaveAndDrop"
        />

        <div class="flex flex-col items-center justify-center space-y-2 text-center">
            <x-icons.upload class="size-10" x-cloak x-show="! uploading" />

            <x-icons.animate-spin class="size-10" x-cloak x-show="uploading" />

            <p>封面圖 (jpg, jpeg or png)</p>

            <template x-if="errorMessage">
                <span class="text-red-500" x-text="errorMessage"></span>
            </template>
        </div>
    </div>
</div>
