import { Modal } from './modal.js';

declare global {
    interface Window {
        codeBlockHelper: (element: HTMLElement) => void;
    }
}

// use Tailwind CSS class names
const BASE_BUTTON_CLASS_NAME: string[] = [
    'size-8',
    'flex',
    'justify-center',
    'items-center',
    'text-gray-50',
    'bg-emerald-500',
    'dark:bg-lividus-500',
    'rounded-md',
    'text-lg',
    'hover:bg-emerald-600',
    'dark:hover:bg-lividus-400',
    'active:bg-emerald-500',
    'dark:active:bg-lividus-500',
    'opacity-0',
    'group-hover:opacity-100',
    'transition-all',
    'duration-200',
    'cursor-pointer',
];

const CHECK_ICON_SVG: string = `
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
  <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
</svg>
`;

const CLIPBOARD_ICON_SVG: string = `
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M10 1.5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zm-5 0A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5v1A1.5 1.5 0 0 1 9.5 4h-3A1.5 1.5 0 0 1 5 2.5zm-2 0h1v1A2.5 2.5 0 0 0 6.5 5h3A2.5 2.5 0 0 0 12 2.5v-1h1a2 2 0 0 1 2 2V14a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V3.5a2 2 0 0 1 2-2"/>
</svg>
`;

const ARROWS_ANGLE_EXPAND_ICON_SVG: string = `
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrows-angle-expand" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M5.828 10.172a.5.5 0 0 0-.707 0l-4.096 4.096V11.5a.5.5 0 0 0-1 0v3.975a.5.5 0 0 0 .5.5H4.5a.5.5 0 0 0 0-1H1.732l4.096-4.096a.5.5 0 0 0 0-.707m4.344-4.344a.5.5 0 0 0 .707 0l4.096-4.096V4.5a.5.5 0 1 0 1 0V.525a.5.5 0 0 0-.5-.5H11.5a.5.5 0 0 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 0 .707"/>
</svg>
`;

function createCopyCodeButton(code: string): HTMLButtonElement {
    // create copy button
    const copyButton: HTMLButtonElement = document.createElement('button');
    // set button position
    copyButton.classList.add(...BASE_BUTTON_CLASS_NAME);
    copyButton.innerHTML = CLIPBOARD_ICON_SVG;

    // when copy button is clicked, copy code to clipboard
    copyButton.addEventListener('click', function (this: HTMLButtonElement) {
        // copy code to clipboard
        navigator.clipboard.writeText(code).then(
            () => console.log('Copied to clipboard'),
            () => console.log('Failed to copy to clipboard'),
        );

        // change button icon to "Copied!" for 2 seconds
        this.innerHTML = CHECK_ICON_SVG;
        setTimeout(
            function (this: HTMLButtonElement) {
                this.innerHTML = CLIPBOARD_ICON_SVG;
            }.bind(this),
            2000,
        );
    });

    return copyButton;
}

function createExpandCodeButton(preOuterHtml: string): HTMLButtonElement {
    const expandCodeButton: HTMLButtonElement =
        document.createElement('button');
    expandCodeButton.classList.add(...BASE_BUTTON_CLASS_NAME);
    expandCodeButton.innerHTML = ARROWS_ANGLE_EXPAND_ICON_SVG;

    const modal = new Modal({ innerHtml: preOuterHtml });

    expandCodeButton.addEventListener(
        'click',
        function (this: HTMLButtonElement) {
            modal.open();
        },
    );

    return expandCodeButton;
}

window.codeBlockHelper = function (element: HTMLElement): void {
    const preTags: HTMLCollectionOf<HTMLPreElement> =
        element.getElementsByTagName('pre');

    // add copy button to all pre tags
    for (const preTag of preTags) {
        if (preTag.classList.contains('code-block-helper-added')) {
            return;
        }

        preTag.classList.add('code-block-helper-added', 'group', 'relative');

        const codes = preTag.getElementsByTagName('code');

        if (codes.length === 0) {
            continue;
        }

        const code: HTMLElement = codes[0];

        code.classList.add('font-jetbrains-mono', 'text-lg', 'font-semibold');

        // start to create copy button...
        const copyButton: HTMLButtonElement = createCopyCodeButton(
            code.innerText,
        );

        const expandCodeButton = createExpandCodeButton(preTag.outerHTML);

        const codeHelperGroup: HTMLDivElement = document.createElement('div');
        codeHelperGroup.classList.add(
            'absolute',
            'top-2',
            'right-2',
            'flex',
            'gap-2',
        );

        preTag.appendChild(codeHelperGroup);

        // append these button in pre tag
        codeHelperGroup.appendChild(copyButton);
        codeHelperGroup.appendChild(expandCodeButton);

        // remove these new element that create in this script,
        // when user want to navigate to next page...
        document.addEventListener(
            'livewire:navigating',
            () => {
                copyButton.remove();
                expandCodeButton.remove();
                codeHelperGroup.remove();
                preTag.classList.remove('code-block-helper-added');
            },
            { once: true },
        );
    }
};
