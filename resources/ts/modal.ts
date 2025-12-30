const BACKGROUND_BACKDROP_CLASS_NAME: string = 'modal-background-backdrop';
const MODAL_PANEL_CLASS_NAME: string = 'modal-panel';
const CLOSE_MODAL_BUTTON_CLASS_NAME: string = 'close-modal-button';
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
    private backgroundBackdrop: HTMLDivElement;
    private modalPanel: HTMLDivElement;
    private closeButton: HTMLButtonElement;
    private abortController: AbortController;
    private readonly scrollbarWidth: number;

    public constructor(
        id: string,
        innerHtml: string,
        customClassName: string[] = [],
    ) {
        this.element = document.createElement('div');

        this.element.id = id;
        this.element.style.display = 'none';
        this.element.innerHTML = this.innerHtmlTemplate(
            innerHtml,
            customClassName,
        );

        document.body.appendChild(this.element);

        this.backgroundBackdrop = this.element.getElementsByClassName(
            BACKGROUND_BACKDROP_CLASS_NAME,
        )[0] as HTMLDivElement;

        this.modalPanel = this.element.getElementsByClassName(
            MODAL_PANEL_CLASS_NAME,
        )[0] as HTMLDivElement;

        this.closeButton = this.element.getElementsByClassName(
            CLOSE_MODAL_BUTTON_CLASS_NAME,
        )[0] as HTMLButtonElement;

        this.scrollbarWidth = window.innerWidth - document.body.clientWidth;

        this.abortController = new AbortController();
    }

    private innerHtmlTemplate(
        innerHtml: string,
        customClassName: string[],
    ): string {
        return `<div class="relative z-30 ${customClassName.join(' ')}">
            <!-- Background backdrop, show/hide based on modal state -->
            <div
                class="${BACKGROUND_BACKDROP_CLASS_NAME} fixed inset-0 bg-zinc-500/75 backdrop-blur-md transition-opacity ${HIDE_BACKGROUND_BACKDROP_CLASS_NAME.join(' ')}"
            ></div>

            <div class="overflow-y-auto fixed inset-0 z-10 w-screen">
                <div class="flex justify-center items-center p-4 min-h-full text-center">
                    <!-- Modal panel, show/hide based on modal state. -->
                    <div
                        class="${MODAL_PANEL_CLASS_NAME} relative transform overflow-hidden rounded-xl text-left transition-all sm:w-fit sm:max-w-6xl ${HIDE_MODAL_PANEL_CLASS_NAME.join(' ')}"
                    >
                        ${innerHtml}
                    </div>
                </div>
            </div>

            <div class="fixed top-10 right-10 z-10">
                <button
                    type="button"
                    class="${CLOSE_MODAL_BUTTON_CLASS_NAME} text-zinc-200 transition duration-300 hover:text-zinc-50 cursor-pointer"
                >
                   ${X_CIRCLE_FILL_ICON_SVG}
                </button>
            </div>
        </div>`;
    }

    private triggerReflow() {
        this.element.offsetHeight;
    }

    public open() {
        this.element.style.display = 'block';
        document.documentElement.style.overflow = 'hidden';
        document.documentElement.style.paddingRight = `${this.scrollbarWidth}px`;

        this.triggerReflow();

        this.backgroundBackdrop.classList.remove(
            ...HIDE_BACKGROUND_BACKDROP_CLASS_NAME,
        );
        this.backgroundBackdrop.classList.add(
            ...SHOW_BACKGROUND_BACKDROP_CLASS_NAME,
        );
        this.modalPanel.classList.remove(...HIDE_MODAL_PANEL_CLASS_NAME);
        this.modalPanel.classList.add(...SHOW_MODAL_PANEL_CLASS_NAME);
        this.closeButton.classList.remove('opacity-0');
        this.closeButton.classList.add('opacity-100');

        this.setupCloseHandlers();
    }

    private setupCloseHandlers() {
        // Close by clicking the backdrop
        this.element.addEventListener('click', () => this.close(), {
            signal: this.abortController.signal,
        });

        // Prevent closing when clicking modal content
        this.modalPanel.addEventListener(
            'click',
            (event: Event) => {
                event.stopPropagation();
            },
            { signal: this.abortController.signal },
        );

        // Close by escape key
        document.addEventListener(
            'keydown',
            (event: KeyboardEvent) => {
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
        // Create a new controller for next time
        this.abortController = new AbortController();

        this.backgroundBackdrop.addEventListener(
            'transitionend',
            (event: TransitionEvent) => {
                if (event.propertyName === 'opacity') {
                    this.element.style.display = 'none';
                    document.documentElement.style.overflow = '';
                    document.documentElement.style.paddingRight = '';
                }
            },
            { once: true },
        );

        this.backgroundBackdrop.classList.remove(
            ...SHOW_BACKGROUND_BACKDROP_CLASS_NAME,
        );
        this.backgroundBackdrop.classList.add(
            ...HIDE_BACKGROUND_BACKDROP_CLASS_NAME,
        );
        this.modalPanel.classList.remove(...SHOW_MODAL_PANEL_CLASS_NAME);
        this.modalPanel.classList.add(...HIDE_MODAL_PANEL_CLASS_NAME);
        this.closeButton.classList.remove('opacity-100');
        this.closeButton.classList.add('opacity-0');
    }

    public remove() {
        this.element.remove();
    }
}
