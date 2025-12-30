<?php

declare(strict_types=1);

use App\Livewire\Forms\PostForm;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public PostForm $form;

    public string $autoSaveKey;

    public Collection $categories;

    public function mount(): void
    {
        $this->autoSaveKey = 'auto_save_user_'.auth()->id().'_create_post';

        $this->form->user_id = auth()->id();

        $this->categories = Category::all(['id', 'name']);

        $this->form->setDataFromAutoSave($this->autoSaveKey);
    }

    // when data update, auto save it to redis
    public function updated(): void
    {
        $this->form->autoSave($this->autoSaveKey);
    }

    public function save(): void
    {
        $post = $this->form->store();

        $this->form->clearAutoSave($this->autoSaveKey);

        $this->dispatch('toast', status: 'success', message: '成功新增文章！');

        $this->redirect($post->link_with_slug, navigate: true);
    }

    public function render()
    {
        return $this->view()->title('新增文章');
    }
};
?>

@assets
{{-- CKEditor --}}
@vite('resources/ts/ckeditor/ckeditor.ts')
{{-- Tagify --}}
@vite(['resources/ts/tagify.ts', 'node_modules/@yaireo/tagify/dist/tagify.css', 'resources/css/custom-tagify.css'])
@endassets

@script
<script>
    Alpine.data('postsCreatePage', () => ({
        showPage: false,
        csrfToken: @js(csrf_token()),
        imageUploadUrl: @js(route('images.store')),
        tagsListUrl: @js(route('api.tags')),
        bodyMaxCharacters: @js($this->form::BODY_MAX_CHARACTER),
        ClassNameToAddOnEditorContent: @js(['rich-text']),
        tags: $wire.entangle('form.tags').live,
        body: $wire.entangle('form.body').live,
        debounce(callback, delay) {
            let timeoutId;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                callback.apply(this, arguments);
            }, delay);
        },
        async init() {
            // init the creation post-page
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
<x-layouts.main>
    <div
        class="container mx-auto grow"
        x-data="postsCreatePage"
        x-cloak
        x-show="showPage"
    >
        <div class="flex justify-center items-stretch space-x-4">
            <div class="hidden xl:block xl:w-1/5"></div>

            <div class="p-2 w-full max-w-3xl xl:p-0">
                <div class="flex flex-col justify-center items-center space-y-6 w-full">
                    {{-- title --}}
                    <div class="flex items-center text-2xl fill-current text-zinc-700 dark:text-zinc-50">
                        <x-icons.pencil class="w-6" />
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
                                <livewire:posts.upload-preview-image wire:model.live="form.preview_url" />

                                {{-- post classfication --}}
                                <div class="col-span-2 md:col-span-1">
                                    <x-select
                                        id="category_id"
                                        name="category_id"
                                        wire:model.change="form.category_id"
                                        required
                                    >
                                        @slot('label')
                                            分類
                                        @endslot

                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>

                                {{-- post private setting --}}
                                <div class="flex col-span-2 items-center md:col-span-1">
                                    <x-checkbox
                                        id="is-private"
                                        name="is-private"
                                        wire:model.change="form.is_private"
                                    >
                                        文章不公開
                                    </x-checkbox>
                                </div>

                                {{-- post title --}}
                                <div class="col-span-2">
                                    <x-input
                                        id="title"
                                        name="title"
                                        type="text"
                                        wire:model.live.debounce.500ms="form.title"
                                        placeholder="文章標題"
                                        required
                                        autofocus
                                    />
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
                                        class="tagify-custom-look dark:border-zinc-600! border-zinc-300! w-full rounded-md bg-white dark:bg-zinc-700"
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
                            <div class="flex justify-between items-center mt-4 xl:hidden">
                                {{-- show characters count --}}
                                <div
                                    class="dark:text-zinc-50"
                                    wire:ignore
                                >
                                    <span class="character-counter"></span>
                                </div>

                                {{-- save button --}}
                                <x-button wire:loading.attr="disabled">
                                    <x-icons.save
                                        class="w-6"
                                        wire:loading.remove
                                    />

                                    <span
                                        class="w-5 h-5"
                                        wire:loading
                                    >
                    <x-icons.animate-spin />
                  </span>

                                    <span class="ml-2">儲存</span>
                                </x-button>
                            </div>
                        </form>
                    </x-card>

                </div>
            </div>

            <x-posts.editor-desktop-side-menu :form-id="'create-post'" />
        </div>
    </div>
</x-layouts.main>
