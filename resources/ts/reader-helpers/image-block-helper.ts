import { Modal } from '../modal.js';
import { button, icon } from '../config.js';

declare global {
    interface Window {
        imageBlockHelper: (element: HTMLElement) => void;
    }
}

function createExpandImageButton(preOuterHtml: string): HTMLButtonElement {
    const expandImageButton: HTMLButtonElement =
        document.createElement('button');
    expandImageButton.classList.add(
        'absolute',
        'top-2',
        'right-2',
        ...button.BASE_CLASS_NAME,
    );
    expandImageButton.innerHTML = icon.ARROWS_ANGLE_EXPAND;

    const modal = new Modal({
        innerHtml: preOuterHtml,
        customClassName: ['font-jetbrains-mono', 'text-xl'],
    });

    expandImageButton.addEventListener(
        'click',
        function (this: HTMLButtonElement) {
            modal.open();
        },
    );

    return expandImageButton;
}

window.imageBlockHelper = function (element: HTMLElement): void {
    const figureTags: HTMLCollectionOf<HTMLElement> =
        element.getElementsByTagName('figure');

    for (const figureTag of figureTags) {
        if (figureTag.classList.contains('image-block-helper-added')) {
            return;
        }

        figureTag.classList.add(
            'image-block-helper-added',
            'group',
            'relative',
        );

        const images = figureTag.getElementsByTagName('img');

        if (images.length === 0) {
            continue;
        }

        const image: HTMLImageElement = images[0];

        const expandImageButton = createExpandImageButton(image.outerHTML);

        figureTag.appendChild(expandImageButton);

        document.addEventListener(
            'livewire:navigating',
            () => {
                expandImageButton.remove();
                figureTag.classList.remove(
                    'image-block-helper-added',
                    'group',
                    'relative',
                );
            },
            { once: true },
        );
    }
};
