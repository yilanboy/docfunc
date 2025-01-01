import { Modal } from './modal.js';

declare global {
    interface Window {
        imageBlockHelper: (element: HTMLElement) => void;
    }
}

const BASE_BUTTON_CLASS_NAME: string[] = [
    'absolute',
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
];

const ARROWS_ANGLE_EXPAND_ICON_SVG: string = `
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrows-angle-expand" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M5.828 10.172a.5.5 0 0 0-.707 0l-4.096 4.096V11.5a.5.5 0 0 0-1 0v3.975a.5.5 0 0 0 .5.5H4.5a.5.5 0 0 0 0-1H1.732l4.096-4.096a.5.5 0 0 0 0-.707m4.344-4.344a.5.5 0 0 0 .707 0l4.096-4.096V4.5a.5.5 0 1 0 1 0V.525a.5.5 0 0 0-.5-.5H11.5a.5.5 0 0 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 0 .707"/>
</svg>
`;

function createExpandImageButton(preOuterHtml: string): HTMLButtonElement {
    const expandImageButton: HTMLButtonElement =
        document.createElement('button');
    expandImageButton.classList.add(
        'top-2',
        'right-2',
        ...BASE_BUTTON_CLASS_NAME,
    );
    expandImageButton.innerHTML = ARROWS_ANGLE_EXPAND_ICON_SVG;

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
