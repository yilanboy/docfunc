@assets
  {{-- CKEditor --}}
  @vite('resources/ts/ckeditor/ckeditor.ts')
  {{-- Tagify --}}
  @vite(['resources/ts/tagify.ts', 'node_modules/@yaireo/tagify/dist/tagify.css'])

  <style>
    /* CKEditor */
    .ck-editor__editable_inline {
      min-height: 500px;
    }

    /* Tagify */
    .tagify-custom-look {
      --tag-border-radius: 6px;
      align-items: center;
      --tag-inset-shadow-size: 3rem;
    }

    .dark .tagify-custom-look {
      --tag-bg: #52525b;
      --tag-hover: #71717a;
      --tag-text-color: #f9fafb;
      --tag-remove-btn-color: #f9fafb;
      --tag-text-color--edit: #f9fafb;
      --input-color: #f9fafb;
      --placeholder-color: #f9fafb;
      --placeholder-color-focus: #f9fafb;
    }

    :root.dark {
      --tagify-dd-bg-color: #52525b;
      --tagify-dd-color-primary: #71717a;
      --tagify-dd-text-color: #f9fafb;
    }
  </style>
@endassets

@script
  <script>
    Alpine.data('createPostPage', () => ({
      showPage: false,
      csrfToken: @js(csrf_token()),
      imageUploadUrl: @js(route('images.store')),
      tagsListUrl: @js(route('api.tags')),
      bodyMaxCharacters: @js($this->form::BODY_MAX_CHARACTER),
      ClassNameToAddOnEditorContent: @js(['rich-text']),
      tags: @entangle('form.tags').live,
      body: @entangle('form.body').live,
      debounce(callback, delay) {
        let timeoutId;
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
          callback.apply(this, arguments);
        }, delay);
      },
      async init() {
        // init the create post page
        const ckeditor = await window.createClassicEditor(
          this.$refs.editor,
          this.bodyMaxCharacters,
          this.imageUploadUrl,
          this.csrfToken
        );

        // set the default value of the editor
        ckeditor.setData(this.body);

        // binding the value of the ckeditor to the livewire attribute 'body'
        ckeditor.model.document.on('change:data', () => {
          this.debounce(() => {
            this.body = ckeditor.getData();
          }, 1000);
        });

        // override editable block style
        ckeditor.ui.view.editable.element
          .parentElement.classList.add(...this.ClassNameToAddOnEditorContent);

        const response = await fetch(this.tagsListUrl);
        const tagsJson = await response.json();

        const tagify = window.createTagify(
          this.$refs.tags,
          tagsJson.data,
          (event) => {
            this.tags = event.detail.value;
          }
        );

        if (this.tags.length !== 0) {
          tagify.addTags(JSON.parse(this.tags));
        }

        document.addEventListener('livewire:navigating', () => {
          ckeditor.destroy();
          tagify.destroy();
        }, {
          once: true
        });

        // After loading page is finished, show the page
        this.showPage = true;
      }
    }));
  </script>
@endscript

{{-- create new post --}}
<x-layouts.layout-main>
  <div
    class="container mx-auto"
    x-data="createPostPage"
    x-cloak
    x-show="showPage"
  >
    <div class="flex items-stretch justify-center space-x-4">
      <div class="hidden xl:block xl:w-1/5"></div>

      <div class="w-full max-w-3xl p-2 xl:p-0">
        <div class="flex w-full flex-col items-center justify-center space-y-6">
          {{-- title --}}
          <div class="flex items-center fill-current text-2xl text-gray-700 dark:text-gray-50">
            <x-icon.pencil class="w-6" />
            <span class="ml-4">新增文章</span>
          </div>

          {{-- editor --}}
          <x-card class="w-full">
            {{-- validation error message --}}
            <x-auth-validation-errors
              class="mb-4"
              :errors="$errors"
            />

            <form
              id="create-post"
              wire:submit="save"
            >
              <div class="grid grid-cols-2 gap-5">
                {{-- post preview image --}}
                <livewire:shared.upload-image wire:model.live="form.preview_url" />

                {{-- post classfication --}}
                <div class="col-span-2 md:col-span-1">
                  <label
                    class="hidden"
                    for="category_id"
                  >分類</label>

                  <select
                    class="form-select h-12 w-full rounded-md border border-gray-300 text-lg focus:border-indigo-300 focus:ring focus:ring-indigo-200/50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-50"
                    id="category_id"
                    name="category_id"
                    wire:model.change="form.category_id"
                    required
                  >
                    @foreach ($categories as $category)
                      <option value="{{ $category->id }}">
                        {{ $category->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                {{-- post private setting --}}
                <div class="col-span-2 flex items-center md:col-span-1">
                  <label
                    class="inline-flex items-center"
                    for="is-private"
                  >
                    <input
                      class="form-checkbox dark:text-lividus-500 dark:focus:border-lividus-700 dark:focus:ring-lividus-800 h-6 w-6 rounded border-gray-300 text-emerald-400 focus:border-emerald-300 focus:ring focus:ring-emerald-200/50 dark:border-gray-600"
                      id="is-private"
                      name="is-private"
                      type="checkbox"
                      wire:model.change="form.is_private"
                    >
                    <span class="ml-2 text-lg text-gray-600 dark:text-gray-50">文章不公開</span>
                  </label>
                </div>

                {{-- post title --}}
                <div class="col-span-2">
                  <label
                    class="hidden"
                    for="title"
                  >文章標題</label>

                  <input
                    class="form-input h-12 w-full rounded-md border border-gray-300 text-lg focus:border-indigo-300 focus:ring focus:ring-indigo-200/50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-50 dark:placeholder-gray-50"
                    id="title"
                    name="title"
                    type="text"
                    wire:model.live.debounce.500ms="form.title"
                    placeholder="文章標題"
                    required
                    autofocus
                  >
                </div>

                {{-- post tags --}}
                <div
                  class="col-span-2"
                  wire:ignore
                >
                  <label
                    class="hidden"
                    for="tags"
                  >標籤 (最多 5 個)</label>

                  <input
                    class="tagify-custom-look w-full rounded-md border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-700"
                    id="tags"
                    type="text"
                    placeholder="標籤 (最多 5 個)"
                    x-ref="tags"
                  >
                </div>

                {{-- post body --}}
                <div
                  class="col-span-2 max-w-none"
                  wire:ignore
                >
                  <div x-ref="editor"></div>
                </div>
              </div>

              {{-- show in mobile device --}}
              <div class="mt-4 flex items-center justify-between xl:hidden">
                {{-- show characters count --}}
                <div
                  class="dark:text-gray-50"
                  wire:ignore
                >
                  <span class="character-counter"></span>
                </div>

                {{-- save button --}}
                <x-button wire:loading.attr="disabled">
                  <x-icon.save
                    class="w-6"
                    wire:loading.remove
                  />

                  <span
                    class="h-5 w-5"
                    wire:loading
                  >
                    <x-icon.animate-spin />
                  </span>

                  <span class="ml-2">儲存</span>
                </x-button>
              </div>
            </form>
          </x-card>

        </div>
      </div>

      <x-post-form.desktop-editor-side-menu :form-id="'create-post'" />
    </div>
  </div>
</x-layouts.layout-main>
