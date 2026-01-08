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
    // create a copy button
    const copyButton: HTMLButtonElement = document.createElement('button');
    // set button position
    copyButton.classList.add(...button.BASE_CLASS_NAME);
    copyButton.innerHTML = icon.CLIPBOARD;

    // when the copy button is clicked, copy code to the clipboard
    copyButton.addEventListener('click', function(this: HTMLButtonElement) {
        // copy code to clipboard
        navigator.clipboard.writeText(code).then(
            () => console.log('Copied to clipboard'),
            () => console.log('Failed to copy to clipboard')
        );

        // change the button icon to "Copied!" for 2 seconds
        this.innerHTML = icon.CHECK;
        setTimeout(
            function(this: HTMLButtonElement) {
                this.innerHTML = icon.CLIPBOARD;
            }.bind(this),
            2000
        );
    });

    return copyButton;
}

function createExpandCodeButton(
    modal: Modal,
    preOuterHTML: string
): HTMLButtonElement {
    const expandCodeButton: HTMLButtonElement =
        document.createElement('button');
    expandCodeButton.classList.add(...button.BASE_CLASS_NAME);
    expandCodeButton.innerHTML = icon.ARROWS_ANGLE_EXPAND;

    const zoomInCode = document.getElementById(
        ZOOM_IN_PRE_ID
    ) as HTMLImageElement;

    expandCodeButton.addEventListener(
        'click',
        function(this: HTMLButtonElement) {
            zoomInCode.innerHTML = preOuterHTML;
            modal.open();
        }
    );

    return expandCodeButton;
}

function getProgramLanguage(element: HTMLPreElement) {
    const foundClass = element.getAttribute('data-program-language');

    if (!foundClass) {
        return 'text';
    }

    return foundClass;
}

// create language label
function createLanguageLabel(language: string): HTMLSpanElement {
    const labelElement: HTMLSpanElement = document.createElement('span');
    labelElement.classList.add('language-label', ...label.BASE_CLASS_NAME);

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

window.codeBlockHelper = function(element: HTMLElement): void {
    const preTags: HTMLCollectionOf<HTMLPreElement> =
        element.getElementsByTagName('pre');

    if (preTags.length === 0) {
        return;
    }

    const zoomInCode: HTMLDivElement = document.createElement('div');
    zoomInCode.classList.add('lg:min-w-3xl');
    zoomInCode.id = ZOOM_IN_PRE_ID;

    const modal = new Modal(ZOOM_IN_PRE_MODAL_ID, zoomInCode.outerHTML);

    document.addEventListener(
        'livewire:navigating',
        () => {
            modal.remove();
        },
        { once: true }
    );

    const marker = 'code-block-helper-added';

    // add a code block helper to all pre-tags
    for (const preTag of preTags) {
        if (preTag.classList.contains(marker)) {
            return;
        }

        // to make the copy button fixed in the container, we wrap it in the container
        let wrapper: HTMLDivElement = document.createElement('div');
        // add 'relative' to make this element to become an anchor
        wrapper.classList.add('group', 'relative');

        // set the wrapper as sibling of the pre-tag
        preTag.parentNode?.insertBefore(wrapper, preTag);
        // set element as child of wrapper
        wrapper.appendChild(preTag);

        preTag.classList.add(marker);

        // to get language from code class name, the class name is like "language-JavaScript"
        // we need to get the last part of the class name
        const language = getProgramLanguage(preTag);

        const codes = preTag.getElementsByTagName('code');

        if (codes.length === 0) {
            continue;
        }

        const code: HTMLElement = codes[0];

        const languageLabelElement: HTMLSpanElement =
            createLanguageLabel(language);

        // start to create the copy button...
        const copyButton: HTMLButtonElement = createCopyCodeButton(
            code.innerText
        );

        const expandCodeButton = createExpandCodeButton(
            modal,
            preTag.outerHTML
        );

        const codeHelperGroup: HTMLDivElement = document.createElement('div');
        codeHelperGroup.classList.add(
            'hidden',
            'lg:flex',
            'gap-2',
            'absolute',
            'top-2',
            'right-2',
            'opacity-0',
            'group-hover:opacity-100',
            'transition-opacity',
            'duration-200'
        );

        wrapper.appendChild(codeHelperGroup);

        // append these buttons in the pre-tag
        // appended language label
        codeHelperGroup.appendChild(languageLabelElement);
        codeHelperGroup.appendChild(copyButton);
        codeHelperGroup.appendChild(expandCodeButton);

        // remove these new element that create in this script
        // when the user wants to navigate to the next page...
        document.addEventListener(
            'livewire:navigating',
            () => {
                languageLabelElement.remove();
                copyButton.remove();
                expandCodeButton.remove();
                codeHelperGroup.remove();
                wrapper.replaceWith(preTag);
                preTag.classList.remove(marker);
            },
            { once: true }
        );
    }
};
