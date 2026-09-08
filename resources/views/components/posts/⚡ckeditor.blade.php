<?php

declare(strict_types=1);

use App\Livewire\Forms\PostForm;
use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component
{
    #[Modelable]
    public string $content = '';

    public int $maxCharacters = PostForm::BODY_MAX_CHARACTER;

    public array $className = ['rich-text'];
};
?>

@assets
    @vite('resources/ts/ckeditor/ckeditor.ts')
@endassets

<script>
    Alpine.data('ckeditorComponent', () => ({
        async init() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const imageUploadUrl = this.$el.dataset.imageUploadUrl;

            const ckeditor = await window.createClassicEditor(
                this.$refs.editor,
                this.$wire.content,
                this.$wire.maxCharacters,
                imageUploadUrl,
                csrfToken,
            );

            const updateContent = window.debounce(() => {
                this.$wire.content = ckeditor.getData();
            }, 1000);

            // binding the value of the ckeditor to the livewire property
            ckeditor.model.document.on('change:data', () => {
                updateContent();
            });

            // override editable block style
            ckeditor.ui.view.editable.element.parentElement.classList.add(...this.$wire.className);

            document.addEventListener(
                'livewire:navigating',
                () => {
                    ckeditor.destroy();
                },
                { once: true },
            );

            this.$dispatch('ckeditor-ready');
        },
    }));
</script>

<div
    data-image-upload-url="{{ route('images.store') }}"
    wire:ignore
    x-data="ckeditorComponent"
>
    <div x-ref="editor"></div>
</div>
