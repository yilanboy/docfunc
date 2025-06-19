import { Modal } from '../modal.js';
import { button, icon } from '../config.js';

declare global {
    interface Window {
        imageBlockHelper: (element: HTMLElement) => void;
    }
}

const ZOOM_IN_IMAGE_MODAL_ID = 'zoom-in-image-modal';
const ZOOM_IN_IMAGE_ID = 'zoom-in-image';

function createExpandImageButton(modal: Modal, src: string): HTMLButtonElement {
    const expandImageButton: HTMLButtonElement =
        document.createElement('button');
    expandImageButton.classList.add(
        'absolute',
        'top-2',
        'right-2',
        ...button.BASE_CLASS_NAME,
    );
    expandImageButton.innerHTML = icon.ARROWS_ANGLE_EXPAND;

    const zoomInImage = document.getElementById(
        ZOOM_IN_IMAGE_ID,
    ) as HTMLImageElement;

    expandImageButton.addEventListener('click', () => {
        zoomInImage.src = src;
        modal.open();
    });

    return expandImageButton;
}

window.imageBlockHelper = function (element: HTMLElement): void {
    const zoomInImage: HTMLImageElement = document.createElement('img');
    zoomInImage.classList.add('lg:min-w-3xl');
    zoomInImage.id = ZOOM_IN_IMAGE_ID;

    const modal = new Modal(ZOOM_IN_IMAGE_MODAL_ID, zoomInImage.outerHTML);

    document.addEventListener(
        'livewire:navigating',
        () => {
            modal.remove();
        },
        { once: true },
    );

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

        const expandImageButton = createExpandImageButton(modal, image.src);

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
