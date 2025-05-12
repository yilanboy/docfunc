@script
  <script>
    Alpine.data('postsUploadPreviewImagePart', () => ({
      isUploading: false,
      progress: 0,
      makeIsUploadingTrue() {
        this.isUploading = true;
      },
      makeIsUploadingFalse() {
        this.isUploading = false;
      },
      updateProgress(event) {
        this.progress = event.detail.progress;
      },
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
      clearImage() {
        this.$wire.set('image', null);
      },
      removePreviewUrl() {
        if (confirm('你確定要刪除預覽圖嗎？')) {
          this.$wire.set('imageUrl', null);
        }
      }
    }));
  </script>
@endscript

<div
  class="col-span-2 text-base"
  x-data="postsUploadPreviewImagePart"
  x-on:livewire-upload-start="makeIsUploadingTrue"
  x-on:livewire-upload-finish="makeIsUploadingFalse"
  x-on:livewire-upload-error="makeIsUploadingFalse"
  x-on:livewire-upload-progress="updateProgress"
>
  @if (!is_null($imageUrl) && is_null($image))
    {{-- image preview --}}
    <div class="relative w-full">
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
  @elseif ($errors->isEmpty() && !is_null($image))
    {{-- uploaded image preview --}}
    <div class="relative w-full">
      <img
        class="rounded-lg"
        id="upload-image"
        src="{{ $image->temporaryUrl() }}"
        alt="preview image"
      >

      <button
        class="hover:backdrop-blur-xs group absolute right-0 top-0 flex h-full w-full items-center justify-center rounded-lg transition-all duration-150 hover:bg-zinc-600/50"
        type="button"
        x-on:click="clearImage"
      >
        <x-icons.x-circle
          class="size-24 opacity-0 transition-all duration-150 group-hover:text-zinc-50 group-hover:opacity-100"
        />
      </button>

      <span
        class="absolute right-2 top-2 inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-sm font-medium text-emerald-700 ring-1 ring-inset ring-emerald-700/10 dark:bg-gray-50 dark:text-gray-700 dark:ring-gray-700/10"
      >預覽圖</span>
    </div>
  @else
    {{-- Upload Area --}}
    <div
      class="relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-dashed border-emerald-500 bg-transparent px-4 py-6 tracking-wide text-emerald-500 transition-all duration-300 hover:border-emerald-600 hover:text-emerald-600 dark:border-indigo-400 dark:text-indigo-400 dark:hover:border-indigo-300 dark:hover:text-indigo-300"
      x-ref="uploadBlock"
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
        <x-icons.upload class="size-10" />

        <p>預覽圖 (jpg, jpeg or png)</p>

        @error('image')
          <span class="text-red-500">{{ $message }}</span>
        @enderror
      </div>
    </div>

    {{-- Progress Bar --}}
    <div
      class="relative mt-4 pt-1"
      x-cloak
      x-show="isUploading"
    >
      <div class="mb-4 flex h-4 overflow-hidden rounded-sm bg-emerald-200 text-xs dark:bg-indigo-200">
        <div
          class="flex flex-col justify-center whitespace-nowrap bg-emerald-500 text-center text-white dark:bg-indigo-500"
          x-bind:style="`width:${progress}%`"
        >
        </div>
      </div>
    </div>
  @endif
</div>
