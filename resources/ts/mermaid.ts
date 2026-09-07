import type Mermaid from 'mermaid';
import { Modal } from './modal.js';
import { button, icon } from './config.js';

declare global {
    interface Window {
        renderMermaidDiagrams: (element: HTMLElement) => Promise<void>;
    }
}

let mermaidInstance: typeof Mermaid | null = null;
let diagramCounter = 0;

const ZOOM_IN_MERMAID_MODAL_ID = 'zoom-in-mermaid-modal';
const ZOOM_IN_MERMAID_ID = 'zoom-in-mermaid';

let zoomInMermaidModal: Modal | null = null;

async function getMermaid(): Promise<typeof Mermaid> {
    if (!mermaidInstance) {
        const module = await import('mermaid');
        mermaidInstance = module.default;
    }

    return mermaidInstance;
}

function initializeMermaid(mermaid: typeof Mermaid): void {
    const isDarkMode =
        document.documentElement.getAttribute('data-theme') === 'dark' ||
        document.documentElement.classList.contains('dark');

    mermaid.initialize({
        startOnLoad: false,
        securityLevel: 'strict',
        theme: isDarkMode ? 'dark' : 'default',
    });
}

export async function renderMermaidSvg(
    mermaid: typeof Mermaid,
    code: string
): Promise<string> {
    initializeMermaid(mermaid);
    const id = `mermaid-diagram-${Date.now()}-${++diagramCounter}`;
    const { svg } = await mermaid.render(id, code);

    return svg;
}

function getOrCreateZoomModal(): Modal {
    if (!zoomInMermaidModal) {
        const zoomInContainer = document.createElement('div');
        zoomInContainer.id = ZOOM_IN_MERMAID_ID;
        zoomInContainer.className =
            'mermaid-modal-content flex max-h-[85vh] w-[90vw] max-w-5xl items-center justify-center overflow-auto rounded-xl border border-zinc-200 bg-white p-6 shadow-2xl transition-colors dark:border-zinc-700 dark:bg-zinc-800';

        zoomInMermaidModal = new Modal(
            ZOOM_IN_MERMAID_MODAL_ID,
            zoomInContainer.outerHTML
        );

        document.addEventListener(
            'livewire:navigating',
            () => {
                zoomInMermaidModal?.remove();
                zoomInMermaidModal = null;
            },
            { once: true }
        );
    }

    return zoomInMermaidModal;
}

export const updateMermaidThemes = async (): Promise<void> => {
    const diagrams = document.querySelectorAll<HTMLElement>(
        '.mermaid-diagram-container'
    );

    if (diagrams.length === 0) {
        return;
    }

    const mermaid = await getMermaid();
    initializeMermaid(mermaid);

    for (const diagram of diagrams) {
        const code = diagram.dataset.mermaidCode;
        if (code) {
            try {
                const svg = await renderMermaidSvg(mermaid, code);
                const svgWrapper = diagram.querySelector('.mermaid-svg-wrapper');
                if (svgWrapper) {
                    svgWrapper.innerHTML = svg;
                } else {
                    diagram.innerHTML = svg;
                }

                const zoomContainer = document.getElementById(ZOOM_IN_MERMAID_ID);
                if (
                    zoomContainer &&
                    zoomInMermaidModal &&
                    zoomInMermaidModal.element.style.display !== 'none'
                ) {
                    zoomContainer.innerHTML = svg;
                }
            } catch (error) {
                console.error(
                    'Mermaid re-rendering failed during theme switch:',
                    error
                );
            }
        }
    }
};

let isObserverInitialized = false;

export function setupThemeObserver(): void {
    if (typeof window === 'undefined' || isObserverInitialized) {
        return;
    }

    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            if (
                mutation.attributeName === 'data-theme' ||
                mutation.attributeName === 'class'
            ) {
                updateMermaidThemes();
                break;
            }
        }
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme', 'class'],
    });
    isObserverInitialized = true;
}

export async function renderMermaidDiagrams(
    element: HTMLElement
): Promise<void> {
    const mermaidBlocks = element.querySelectorAll<HTMLElement>(
        'pre code.language-mermaid'
    );

    if (mermaidBlocks.length === 0) {
        return;
    }

    const mermaid = await getMermaid();
    setupThemeObserver();

    for (const block of mermaidBlocks) {
        const code = block.innerText;
        const preElement = block.parentElement as HTMLPreElement | null;
        if (!preElement) {
            continue;
        }

        try {
            const svg = await renderMermaidSvg(mermaid, code);

            const diagramContainer = document.createElement('div');
            diagramContainer.classList.add(
                'mermaid-diagram-container',
                'group',
                'relative',
                'my-6',
                'flex',
                'justify-center',
                'rounded-xl',
                'border',
                'border-zinc-200',
                'bg-white/80',
                'p-4',
                'shadow-2xs',
                'dark:border-zinc-700',
                'dark:bg-zinc-800/80'
            );
            diagramContainer.dataset.mermaidCode = code;

            const toolbar = document.createElement('div');
            toolbar.className =
                'absolute top-2 right-2 z-10 flex opacity-0 transition-opacity duration-200 group-hover:opacity-100 focus-within:opacity-100';

            const zoomBtn = document.createElement('button');
            zoomBtn.type = 'button';
            zoomBtn.classList.add(...button.BASE_CLASS_NAME);
            zoomBtn.title = 'Zoom diagram';
            zoomBtn.setAttribute('aria-label', 'Zoom diagram');
            zoomBtn.innerHTML = icon.ARROWS_ANGLE_EXPAND;

            zoomBtn.addEventListener('click', () => {
                const modal = getOrCreateZoomModal();
                const currentSvg =
                    diagramContainer.querySelector('.mermaid-svg-wrapper')
                        ?.innerHTML ?? svg;
                const zoomContainer = document.getElementById(
                    ZOOM_IN_MERMAID_ID
                );
                if (zoomContainer) {
                    zoomContainer.innerHTML = currentSvg;
                }
                modal.open();
            });

            toolbar.appendChild(zoomBtn);

            const svgWrapper = document.createElement('div');
            svgWrapper.className =
                'mermaid-svg-wrapper flex w-full justify-center overflow-x-auto';
            svgWrapper.innerHTML = svg;

            diagramContainer.appendChild(toolbar);
            diagramContainer.appendChild(svgWrapper);

            preElement.replaceWith(diagramContainer);
        } catch (error) {
            console.error('Mermaid rendering failed:', error);
            const errorContainer = document.createElement('div');
            errorContainer.classList.add(
                'mermaid-error',
                'text-red-500',
                'font-mono',
                'text-sm',
                'p-4',
                'my-6',
                'rounded-xl',
                'bg-red-100',
                'dark:bg-red-900/30'
            );
            errorContainer.innerText = `Error rendering diagram:\n${(error as Error).message}`;
            preElement.replaceWith(errorContainer);
        }
    }
}

window.renderMermaidDiagrams = renderMermaidDiagrams;
