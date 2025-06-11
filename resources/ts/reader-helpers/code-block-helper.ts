import { Modal } from '../modal.js';
import { button, icon, label, languageSettings } from '../config.js';

declare global {
    interface Window {
        codeBlockHelper: (element: HTMLElement) => void;
    }
}

const ZOOM_IN_PRE_MODAL_ID = 'zoom-in-pre-modal';
const ZOOM_IN_PRE_ID = 'zoom-in-pre';

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

function createExpandCodeButton(
    modal: Modal,
    codeOuterHTML: string,
): HTMLButtonElement {
    const expandCodeButton: HTMLButtonElement =
        document.createElement('button');
    expandCodeButton.classList.add(...button.BASE_CLASS_NAME);
    expandCodeButton.innerHTML = icon.ARROWS_ANGLE_EXPAND;

    const zoomInCode = document.getElementById(
        ZOOM_IN_PRE_ID,
    ) as HTMLImageElement;

    expandCodeButton.addEventListener(
        'click',
        function (this: HTMLButtonElement) {
            zoomInCode.innerHTML = codeOuterHTML;
            modal.open();
        },
    );

    return expandCodeButton;
}

function findLanguagePrefixClass(element: HTMLElement) {
    const prefix = 'language-';

    const foundClass = Array.from(element.classList).find((className) =>
        className.startsWith(prefix),
    );

    if (!foundClass) {
        return 'text';
    }

    return foundClass.substring(prefix.length);
}

// create language label
function createLanguageLabel(language: string): HTMLSpanElement {
    const labelElement: HTMLSpanElement = document.createElement('span');
    labelElement.classList.add(...label.BASE_CLASS_NAME);

    if (languageSettings[language]) {
        labelElement.innerText = languageSettings[language].label;
        labelElement.style.backgroundColor =
            languageSettings[language].backgroundColor;
        labelElement.style.color = languageSettings[language].color;
    } else {
        labelElement.innerText = language;
    }

    return labelElement;
}

window.codeBlockHelper = function (element: HTMLElement): void {
    const zoomInCode: HTMLPreElement = document.createElement('pre');
    zoomInCode.classList.add('min-w-3xl');
    zoomInCode.id = ZOOM_IN_PRE_ID;

    const modal = new Modal({
        id: ZOOM_IN_PRE_MODAL_ID,
        innerHtml: zoomInCode.outerHTML,
    });

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

        // get language from code class name, the class name is like "language-javascript"
        // we need to get the last part of the class name

        const language = findLanguagePrefixClass(code);

        const languageLabelElement: HTMLSpanElement =
            createLanguageLabel(language);

        // start to create copy button...
        const copyButton: HTMLButtonElement = createCopyCodeButton(
            code.innerText,
        );

        const expandCodeButton = createExpandCodeButton(modal, code.outerHTML);

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
        // append language label
        codeHelperGroup.appendChild(languageLabelElement);
        codeHelperGroup.appendChild(copyButton);
        codeHelperGroup.appendChild(expandCodeButton);

        // remove these new element that create in this script,
        // when user want to navigate to next page...
        document.addEventListener(
            'livewire:navigating',
            () => {
                languageLabelElement.remove();
                copyButton.remove();
                expandCodeButton.remove();
                codeHelperGroup.remove();
                modal.remove();
                preTag.classList.remove('code-block-helper-added');
            },
            { once: true },
        );
    }
};
