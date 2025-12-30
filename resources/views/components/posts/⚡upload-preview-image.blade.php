<?php

declare(strict_types=1);

use App\Services\FileService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Random\RandomException;

new class extends Component
{
    use WithFileUploads;

    #[Validate(
        rule: ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
        message: [
            'image' => '必須是圖片',
            'mimes' => '圖片格式必須是 jpeg, png, jpg',
            'max'   => '圖片大小不能超過 1024 KB',
        ],
    )]
    public $image;

    #[Modelable]
    public ?string $imageUrl = null;

    public function store(): void
    {
        $this->validate();

        if (is_null($this->image)) {
            return;
        }

        $imageName = app(FileService::class)->generateFileName($this->image->getClientOriginalExtension());

        $path = $this->image->storeAs('images', $imageName, config('filesystems.default'));

        $this->imageUrl = Storage::disk()->url($path);
    }

    public function updatedImage(): void
    {
        $this->store();
        $this->image = null;
    }
};
?>

@script
<script>
    Alpine.data('postsUploadPreviewImagePart', () => ({
        uploading: false,
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
                this.$wire.$set('imageUrl', null);
            }
        }
    }));
</script>
@endscript

<div
    class="col-span-2 text-base"
    x-data="postsUploadPreviewImagePart"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
    x-on:livewire-upload-error="uploading = false"
>
    {{-- image preview --}}
    <div
        class="relative w-full"
        x-cloak
        x-show="$wire.$errors.all().length === 0 && $wire.imageUrl !== null"
    >
        <img
            class="rounded-lg"
            id="image-url"
            src="{{ $imageUrl }}"
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
        x-show="$wire.imageUrl === null"
    >
        <input
            class="absolute inset-0 z-50 p-0 m-0 w-full h-full opacity-0 cursor-pointer outline-hidden"
            type="file"
            wire:model.live="image"
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

            @error('image')
            <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>
