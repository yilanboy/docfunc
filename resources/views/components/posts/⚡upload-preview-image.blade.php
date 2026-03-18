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
    Alpine.data('uploadPreviewImage', () => ({
        imageUrl: $wire.entangle('imageUrl'),
        uploading: false,
        errorMessage: null,
        changeBlockStyleWhenDragEnter() {
            this.$refs.uploadBlock.classList.remove('text-emerald-500', 'dark:text-indigo-400', 'border-emerald-500',
                'dark:border-indigo-400');
            this.$refs.uploadBlock.classList.add('text-emerald-600', 'dark:text-indigo-300', 'border-emerald-600',
                'dark:border-indigo-300');
        },
        changeBlockStyleWhenDragLeaveAndDrop() {
            this.$refs.uploadBlock.classList.add('text-emerald-500', 'dark:text-indigo-400', 'border-emerald-500',
                'dark:border-indigo-400');
            this.$refs.uploadBlock.classList.remove('text-emerald-600', 'dark:text-indigo-300', 'border-emerald-600',
                'dark:border-indigo-300');
        },
        removePreviewUrl() {
            if (confirm('你確定要刪除預覽圖嗎？')) {
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
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
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
        }
    }));
</script>
@endscript

<div
    class="col-span-2 text-base"
    x-data="uploadPreviewImage"
>
    {{-- image preview --}}
    <div
        class="relative w-full"
        x-cloak
        x-show="imageUrl !== null"
    >
        <img
            class="rounded-lg"
            id="image-url"
            x-bind:src="imageUrl"
            alt="image url"
        >

        <button
            class="flex absolute top-0 right-0 justify-center items-center w-full h-full rounded-lg transition-all duration-150 cursor-pointer group hover:backdrop-blur-xs hover:bg-zinc-600/50"
            type="button"
            x-on:click="removePreviewUrl"
        >
            <x-icons.x-circle
                class="opacity-0 transition-all duration-150 group-hover:opacity-100 size-24 group-hover:text-zinc-50"
            />
        </button>

        <span
            class="inline-flex absolute top-2 right-2 items-center py-1 px-2 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-md ring-1 ring-inset dark:text-gray-700 dark:bg-gray-50 ring-emerald-700/10 dark:ring-gray-700/10"
        >預覽圖</span>
    </div>

    {{-- Upload Area --}}
    <div
        class="flex relative flex-col items-center py-6 px-4 tracking-wide text-emerald-500 bg-transparent rounded-lg border-2 border-emerald-500 border-dashed transition-all duration-300 cursor-pointer dark:text-indigo-400 dark:border-indigo-400 hover:text-emerald-600 hover:border-emerald-600 dark:hover:border-indigo-300 dark:hover:text-indigo-300"
        x-ref="uploadBlock"
        x-cloak
        x-show="imageUrl === null"
    >
        <input
            class="absolute inset-0 z-50 p-0 m-0 w-full h-full opacity-0 cursor-pointer outline-hidden"
            type="file"
            x-on:change="uploadImage"
            x-on:dragenter="changeBlockStyleWhenDragEnter"
            x-on:dragleave="changeBlockStyleWhenDragLeaveAndDrop"
            x-on:drop="changeBlockStyleWhenDragLeaveAndDrop"
        >

        <div class="flex flex-col justify-center items-center space-y-2 text-center">
            <x-icons.upload
                class="size-10"
                x-cloak
                x-show="!uploading"
            />

            <x-icons.animate-spin
                class="size-10"
                x-cloak
                x-show="uploading"
            />

            <p>預覽圖 (jpg, jpeg or png)</p>

            <template x-if="errorMessage">
                <span
                    class="text-red-500"
                    x-text="errorMessage"
                ></span>
            </template>
        </div>
    </div>
</div>
