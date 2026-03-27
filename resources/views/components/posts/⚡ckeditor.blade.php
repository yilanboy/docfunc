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

@script
<script>
    Alpine.data('ckeditorComponent', () => ({
        csrfToken: @js(csrf_token()),
        imageUploadUrl: @js(route('images.store')),
        async init() {
            const ckeditor = await window.createClassicEditor(
                this.$refs.editor,
                this.$wire.maxCharacters,
                this.imageUploadUrl,
                this.csrfToken
            );

            // set the default value of the editor
            ckeditor.setData(this.$wire.content);

            const updateContent = window.debounce(() => {
                this.$wire.content = ckeditor.getData();
            }, 1000);

            // binding the value of the ckeditor to the livewire property
            ckeditor.model.document.on('change:data', () => {
                updateContent();
            });

            // override editable block style
            ckeditor.ui.view.editable.element
                .parentElement.classList.add(...this.$wire.className);

            document.addEventListener('livewire:navigating', () => {
                ckeditor.destroy();
            }, { once: true });
        }
    }));
</script>
@endscript

<div
    x-data="ckeditorComponent"
    wire:ignore
>
    <div x-ref="editor"></div>
</div>
