import type { HeadingOption } from 'ckeditor5';
import {
    Alignment,
    Autoformat,
    BlockQuote,
    Bold,
    ClassicEditor as ClassicEditorBase,
    Code,
    CodeBlock,
    Essentials,
    FindAndReplace,
    Font,
    Heading,
    HeadingButtonsUI,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    ParagraphButtonUI,
    PasteFromOffice,
    PictureEditing,
    SimpleUploadAdapter,
    Strikethrough,
    Table,
    TableToolbar,
    TextTransformation,
    Underline,
    Undo,
    WordCount,
} from 'ckeditor5';

import coreTranslations from 'ckeditor5/translations/zh.js';

import 'ckeditor5/ckeditor5-editor.css';
// Override the default styles.
// import './custom.css';
import { languageSettings } from '../config.js';

class ClassicEditor extends ClassicEditorBase {}

ClassicEditor.builtinPlugins = [
    Alignment,
    Autoformat,
    BlockQuote,
    Bold,
    Code,
    CodeBlock,
    Essentials,
    FindAndReplace,
    Font,
    Heading,
    HeadingButtonsUI,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    ParagraphButtonUI,
    PasteFromOffice,
    PictureEditing,
    SimpleUploadAdapter,
    Strikethrough,
    Table,
    TableToolbar,
    TextTransformation,
    Underline,
    Undo,
    WordCount,
];

ClassicEditor.defaultConfig = {
    toolbar: {
        items: [
            'paragraph',
            'heading2',
            'heading3',
            '|',
            'fontSize',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'link',
            'code',
            '|',
            'bulletedList',
            'numberedList',
            '|',
            'alignment',
            '|',
            'indent',
            'outdent',
            '-',
            'blockQuote',
            'codeBlock',
            '|',
            'insertTable',
            'imageInsert',
            'mediaEmbed',
            '|',
            'undo',
            'redo',
            '|',
            'findAndReplace',
        ],
        shouldNotGroupWhenFull: true,
    },
    heading: {
        options: [
            {
                model: 'paragraph',
                title: 'Paragraph',
                class: 'ck-heading_paragraph',
            },
            {
                model: 'heading2',
                view: 'h2',
                title: 'Heading 2',
                class: 'ck-heading_heading2',
            },
            {
                model: 'heading3',
                view: 'h3',
                title: 'Heading 3',
                class: 'ck-heading_heading3',
            },
        ] as HeadingOption[],
    },
    fontSize: {
        options: ['tiny', 'default', 'big'],
    },
    link: {
        addTargetToExternalLinks: true,
    },
    image: {
        resizeOptions: [
            {
                name: 'resizeImage:original',
                value: null,
                icon: 'original',
            },
            {
                name: 'resizeImage:50',
                value: '50',
                icon: 'medium',
            },
            {
                name: 'resizeImage:75',
                value: '75',
                icon: 'large',
            },
        ],
        toolbar: ['toggleImageCaption', 'imageTextAlternative', 'resizeImage'],
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
    },
    codeBlock: {
        languages: Object.keys(languageSettings).map((key) => ({
            language: key,
            label: languageSettings[key].label,
        })),
        indentSequence: '    ',
    },
    translations: [coreTranslations],
    language: 'zh',
};

declare global {
    interface Window {
        createClassicEditor: (
            element: HTMLElement,
            maxCharacters: number,
            imageUploadUrl: string,
            csrfToken: string,
        ) => Promise<ClassicEditor>;
    }
}

window.createClassicEditor = async function (
    element: HTMLElement,
    maxCharacters: number,
    imageUploadUrl: string,
    csrfToken: string,
) {
    return ClassicEditor.create(element, {
        licenseKey: 'GPL',
        placeholder: '分享使自己成長～',
        // Editor configuration.
        wordCount: {
            onUpdate: (stats) => {
                let characterCounter =
                    document.querySelectorAll('.character-counter');
                // The character count has exceeded the maximum limit
                let isLimitExceeded = stats.characters > maxCharacters;
                // The character count is approaching the maximum limit
                let isCloseToLimit =
                    !isLimitExceeded && stats.characters > maxCharacters * 0.8;

                // update character count in HTML element
                characterCounter.forEach((element) => {
                    element.textContent = `${stats.characters} / ${maxCharacters}`;
                    // If the character count is approaching the limit
                    // add the class 'text-yellow-500' to the 'wordsBox' element to turn the text yellow
                    element.classList.toggle('text-yellow-500', isCloseToLimit);
                    // If the character count exceeds the limit
                    // add the class 'text-red-400' to the 'wordsBox' element to turn the text red
                    element.classList.toggle('text-red-400', isLimitExceeded);
                });
            },
        },
        simpleUpload: {
            // The URL that the images are uploaded to.
            uploadUrl: imageUploadUrl,

            // laravel sanctum need csrf token to authenticate
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        },
    });
};
