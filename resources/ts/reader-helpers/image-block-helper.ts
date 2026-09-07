import { Modal } from '../modal.js';
import { button, icon } from '../config.js';

declare global {
    interface Window {
        imageBlockHelper: (element: HTMLElement) => void;
    }
}

const ZOOM_IN_IMAGE_MODAL_ID = 'zoom-in-image-modal';
const ZOOM_IN_IMAGE_ID = 'zoom-in-image';

let zoomInImageModal: Modal | null = null;

function createExpandImageButton(modal: Modal, src: string, alt: string): HTMLButtonElement {
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
        zoomInImage.alt = alt;
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

    if (!zoomInImageModal) {
        const zoomInImage: HTMLImageElement = document.createElement('img');
        zoomInImage.classList.add('lg:min-w-3xl');
        zoomInImage.id = ZOOM_IN_IMAGE_ID;

        zoomInImageModal = new Modal(ZOOM_IN_IMAGE_MODAL_ID, zoomInImage.outerHTML);

        document.addEventListener(
            'livewire:navigating',
            () => {
                zoomInImageModal?.remove();
                zoomInImageModal = null;
            },
            { once: true },
        );
    }

    const modal = zoomInImageModal;
    const cleanups: Array<() => void> = [];

    for (const figureTag of figureTags) {
        if (figureTag.classList.contains('image-block-helper-added')) {
            continue;
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

        const expandImageButton = createExpandImageButton(modal, image.src, image.alt || '');
        expandImageButton.classList.remove('flex');
        expandImageButton.classList.add(
            'hidden',
            'lg:flex',
            'opacity-0',
            'group-hover:opacity-100',
            'transition-opacity',
            'duration-200',
        );

        figureTag.appendChild(expandImageButton);

        cleanups.push(() => {
            expandImageButton.remove();
            figureTag.classList.remove(
                'image-block-helper-added',
                'group',
                'relative',
            );
        });
    }

    if (cleanups.length > 0) {
        document.addEventListener(
            'livewire:navigating',
            () => {
                for (const cleanup of cleanups) {
                    cleanup();
                }
            },
            { once: true },
        );
    }
};
