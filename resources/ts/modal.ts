const BACKGROUND_BACKDROP_ID: string = 'modal-background-backdrop';
const MODAL_PANEL_ID: string = 'modal-panel';
const CLOSE_MODAL_BUTTON_ID: string = 'close-modal-button';
const X_CIRCLE_FILL_ICON_SVG: string = `
<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="size-10" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
</svg>
`;
const SHOW_BACKGROUND_BACKDROP_CLASS_NAME: string[] = [
    'ease-out',
    'duration-300',
    'opacity-100',
];
const HIDE_BACKGROUND_BACKDROP_CLASS_NAME: string[] = [
    'ease-in',
    'duration-200',
    'opacity-0',
];
const SHOW_MODAL_PANEL_CLASS_NAME: string[] = [
    'ease-out',
    'duration-300',
    'opacity-100',
    'translate-y-0',
    'sm:scale-100',
];
const HIDE_MODAL_PANEL_CLASS_NAME: string[] = [
    'ease-in',
    'duration-200',
    'opacity-0',
    'translate-y-4',
    'sm:translate-y-0',
    'sm:scale-95',
];

export class Modal {
    public element: HTMLDivElement;
    private abortController: AbortController;

    public constructor({
        innerHtml,
        customClassName = [],
    }: {
        innerHtml: string;
        customClassName?: string[];
    }) {
        this.element = document.createElement('div');
        this.element.id = 'dynamic-content-modal';
        this.element.innerHTML = this.innerHtmlTemplate(
            innerHtml,
            customClassName,
        );

        this.abortController = new AbortController();
    }

    public innerHtmlTemplate(
        innerHtml: string,
        customClassName: string[],
    ): string {
        return `<div class="relative z-30 ${customClassName.join(' ')}">
            <!-- Background backdrop, show/hide based on modal state -->
            <div
                id="${BACKGROUND_BACKDROP_ID}"
                class="fixed inset-0 bg-gray-500/75 backdrop-blur-md transition-opacity ${HIDE_BACKGROUND_BACKDROP_CLASS_NAME.join(' ')}"
            ></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <!-- Modal panel, show/hide based on modal state. -->
                    <div
                        id="${MODAL_PANEL_ID}"
                        class="relative transform overflow-hidden rounded-xl text-left transition-all sm:w-fit sm:max-w-6xl ${HIDE_MODAL_PANEL_CLASS_NAME.join(' ')}"
                    >
                        ${innerHtml}
                    </div>
                </div>
            </div>

            <div class="fixed right-10 top-10 z-10">
                <button
                    id="${CLOSE_MODAL_BUTTON_ID}"
                    type="button"
                    class="text-gray-200 transition duration-300 hover:text-gray-50 cursor-pointer"
                >
                   ${X_CIRCLE_FILL_ICON_SVG}
                </button>
            </div>
        </div>`;
    }

    public open() {
        // transport modal to another part of the DOM on the page entirely
        document.body.appendChild(this.element);
        document.body.style.overflow = 'hidden';

        const backgroundBackdrop = document.getElementById(
            BACKGROUND_BACKDROP_ID,
        );

        const modalPanel = document.getElementById(MODAL_PANEL_ID);

        const closeButton = document.getElementById(CLOSE_MODAL_BUTTON_ID);

        if (!backgroundBackdrop || !modalPanel || !closeButton) {
            return;
        }

        setTimeout(() => {
            backgroundBackdrop.classList.remove(
                ...HIDE_BACKGROUND_BACKDROP_CLASS_NAME,
            );
            backgroundBackdrop.classList.add(
                ...SHOW_BACKGROUND_BACKDROP_CLASS_NAME,
            );
            modalPanel.classList.remove(...HIDE_MODAL_PANEL_CLASS_NAME);
            modalPanel.classList.add(...SHOW_MODAL_PANEL_CLASS_NAME);
            closeButton.classList.remove('opacity-0');
            closeButton.classList.add('opacity-100');
        }, 100);

        // Add event listeners for closing if needed
        this.setupCloseHandlers();
    }

    private setupCloseHandlers() {
        // Add close button handler
        const closeButton = document.getElementById(CLOSE_MODAL_BUTTON_ID);

        closeButton?.addEventListener('click', () => this.close(), {
            signal: this.abortController.signal,
        });

        // Optional: Close on escape key
        document.addEventListener(
            'keydown',
            (event) => {
                if (event.key === 'Escape') {
                    this.close();
                }
            },
            { signal: this.abortController.signal },
        );
    }

    private close() {
        // Abort all event listeners
        this.abortController.abort();
        // Create new controller for next time
        this.abortController = new AbortController();

        const backgroundBackdrop = document.getElementById(
            BACKGROUND_BACKDROP_ID,
        );

        const modalPanel = document.getElementById(MODAL_PANEL_ID);

        const closeButton = document.getElementById(CLOSE_MODAL_BUTTON_ID);

        if (!backgroundBackdrop || !modalPanel || !closeButton) {
            return;
        }

        backgroundBackdrop.classList.remove(
            ...SHOW_BACKGROUND_BACKDROP_CLASS_NAME,
        );
        backgroundBackdrop.classList.add(
            ...HIDE_BACKGROUND_BACKDROP_CLASS_NAME,
        );
        modalPanel.classList.remove(...SHOW_MODAL_PANEL_CLASS_NAME);
        modalPanel.classList.add(...HIDE_MODAL_PANEL_CLASS_NAME);
        closeButton.classList.remove('opacity-100');
        closeButton.classList.add('opacity-0');

        setTimeout(() => {
            document.body.removeChild(this.element);
            document.body.style.overflow = '';
        }, 300);
    }
}
