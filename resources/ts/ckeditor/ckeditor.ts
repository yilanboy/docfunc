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
    HeadingOption,
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
    WordCount,
} from 'ckeditor5';

import coreTranslations from 'ckeditor5/translations/zh.js';

import 'ckeditor5/ckeditor5.css';
// Override the default styles.
import './custom.css';

class ClassicEditor extends ClassicEditorBase {}

ClassicEditor.builtinPlugins = [
    Essentials,
    Autoformat,
    Bold,
    Italic,
    BlockQuote,
    Heading,
    Image,
    ImageCaption,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Indent,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    PictureEditing,
    Table,
    TableToolbar,
    TextTransformation,
    HeadingButtonsUI,
    ParagraphButtonUI,
    IndentBlock,
    Code,
    Alignment,
    CodeBlock,
    Underline,
    Strikethrough,
    WordCount,
    ImageInsert,
    Font,
    FindAndReplace,
    SimpleUploadAdapter,
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
            'link',
            'blockQuote',
            'imageInsert',
            'insertTable',
            'mediaEmbed',
            'codeBlock',
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
        languages: [
            { language: 'text', label: 'Plain text' }, // The default language.
            { language: 'bash', label: 'Bash' },
            { language: 'c', label: 'C' },
            { language: 'cs', label: 'C#' },
            { language: 'cpp', label: 'C++' },
            { language: 'css', label: 'CSS' },
            { language: 'dart', label: 'Dart' },
            { language: 'dockerfile', label: 'Dockerfile' },
            { language: 'go', label: 'Go' },
            { language: 'hcl', label: 'HCL' },
            { language: 'html', label: 'HTML' },
            { language: 'ini', label: 'INI' },
            { language: 'java', label: 'Java' },
            { language: 'javascript', label: 'JavaScript' },
            { language: 'json', label: 'JSON' },
            { language: 'kotlin', label: 'Kotlin' },
            { language: 'nginx', label: 'Nginx Config' },
            { language: 'php', label: 'PHP' },
            { language: 'python', label: 'Python' },
            { language: 'ruby', label: 'Ruby' },
            { language: 'rust', label: 'Rust' },
            { language: 'shell', label: 'Shell' },
            { language: 'sql', label: 'SQL' },
            { language: 'swift', label: 'Swift' },
            { language: 'toml', label: 'TOML' },
            { language: 'typescript', label: 'TypeScript' },
            { language: 'xml', label: 'XML' },
            { language: 'yaml', label: 'YAML' },
        ],
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
