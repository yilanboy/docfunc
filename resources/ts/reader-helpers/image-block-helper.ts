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
    const figureTags: HTMLCollectionOf<HTMLElement> =
        element.getElementsByTagName('figure');

    if (figureTags.length === 0) {
        return;
    }

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

    for (const figureTag of figureTags) {
        if (figureTag.classList.contains('image-block-helper-added')) {
            return;
        }

        const images = figureTag.getElementsByTagName('img');

        if (images.length === 0) {
            continue;
        }

        figureTag.classList.add(
            'image-block-helper-added',
            'group',
            'relative',
        );

        const image: HTMLImageElement = images[0];

        const expandImageButton = createExpandImageButton(modal, image.src);
        expandImageButton.classList.remove('flex');
        expandImageButton.classList.add(
            'hidden',
            'lg:flex',
            'group-hover:opacity-100',
            'opacity-0',
            'group-hover:opacity-100',
            'transition-opacity',
            'duration-200',
        );

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
