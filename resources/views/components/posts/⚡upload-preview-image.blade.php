<?php

declare(strict_types=1);

use App\Services\FileService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Random\RandomException;

new class extends Component {
    use WithFileUploads;

    #[
        Validate(
            ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
            message: [
                'image' => '必須是圖片',
                'mimes' => '圖片格式必須是 jpeg, png, jpg',
                'max' => '圖片大小不能超過 1024 KB',
            ],
        ),
    ]
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
          $wire.set('imageUrl', null);
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
      class="hover:backdrop-blur-xs group absolute right-0 top-0 flex h-full w-full cursor-pointer items-center justify-center rounded-lg transition-all duration-150 hover:bg-zinc-600/50"
      type="button"
      x-on:click="removePreviewUrl"
    >
      <x-icons.x-circle
        class="size-24 opacity-0 transition-all duration-150 group-hover:text-zinc-50 group-hover:opacity-100"
      />
    </button>

    <span
      class="absolute right-2 top-2 inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-sm font-medium text-emerald-700 ring-1 ring-inset ring-emerald-700/10 dark:bg-gray-50 dark:text-gray-700 dark:ring-gray-700/10"
    >預覽圖</span>
  </div>

  {{-- Upload Area --}}
  <div
    class="relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-dashed border-emerald-500 bg-transparent px-4 py-6 tracking-wide text-emerald-500 transition-all duration-300 hover:border-emerald-600 hover:text-emerald-600 dark:border-indigo-400 dark:text-indigo-400 dark:hover:border-indigo-300 dark:hover:text-indigo-300"
    x-ref="uploadBlock"
    x-cloak
    x-show="$wire.imageUrl === null"
  >
    <input
      class="outline-hidden absolute inset-0 z-50 m-0 h-full w-full cursor-pointer p-0 opacity-0"
      type="file"
      wire:model.live="image"
      x-on:dragenter="changeBlockStyleWhenDragEnter"
      x-on:dragleave="changeBlockStyleWhenDragLeaveAndDrop"
      x-on:drop="changeBlockStyleWhenDragLeaveAndDrop"
    >

    <div class="flex flex-col items-center justify-center space-y-2 text-center">
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
