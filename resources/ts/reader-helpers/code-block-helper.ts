import { Modal } from '../modal.js';
import { button, icon } from './config.js';

declare global {
    interface Window {
        codeBlockHelper: (element: HTMLElement) => void;
    }
}

function createCopyCodeButton(code: string): HTMLButtonElement {
    // create copy button
    const copyButton: HTMLButtonElement = document.createElement('button');
    // set button position
    copyButton.classList.add(...button.BASE_CLASS_NAME);
    copyButton.innerHTML = icon.CLIPBOARD;

    // when copy button is clicked, copy code to clipboard
    copyButton.addEventListener('click', function (this: HTMLButtonElement) {
        // copy code to clipboard
        navigator.clipboard.writeText(code).then(
            () => console.log('Copied to clipboard'),
            () => console.log('Failed to copy to clipboard'),
        );

        // change button icon to "Copied!" for 2 seconds
        this.innerHTML = icon.CHECK;
        setTimeout(
            function (this: HTMLButtonElement) {
                this.innerHTML = icon.CLIPBOARD;
            }.bind(this),
            2000,
        );
    });

    return copyButton;
}

function createExpandCodeButton(preOuterHtml: string): HTMLButtonElement {
    const expandCodeButton: HTMLButtonElement =
        document.createElement('button');
    expandCodeButton.classList.add(...button.BASE_CLASS_NAME);
    expandCodeButton.innerHTML = icon.ARROWS_ANGLE_EXPAND;

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
