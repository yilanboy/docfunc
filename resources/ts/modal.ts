const MODAL_PANEL_CLASS_NAME: string = "modal-panel";
const CLOSE_MODAL_BUTTON_CLASS_NAME: string = "close-modal-button";
const X_CIRCLE_FILL_ICON_SVG: string = `
<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="size-10" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
</svg>
`;
const SHOW_BACKDROP_CLASS_NAME: string[] = [
    "backdrop:ease-out",
    "backdrop:duration-300",
    "backdrop:opacity-100",
];
const HIDE_BACKDROP_CLASS_NAME: string[] = [
    "backdrop:ease-in",
    "backdrop:duration-200",
    "backdrop:opacity-0",
];
const SHOW_MODAL_PANEL_CLASS_NAME: string[] = [
    "ease-out",
    "duration-300",
    "opacity-100",
    "translate-y-0",
    "sm:scale-100",
];
const HIDE_MODAL_PANEL_CLASS_NAME: string[] = [
    "ease-in",
    "duration-200",
    "opacity-0",
    "translate-y-4",
    "sm:translate-y-0",
    "sm:scale-95",
];

export class Modal {
    public dialogElement: HTMLDialogElement;
    private modalPanel: HTMLDivElement;
    private closeButton: HTMLButtonElement;
    private abortController: AbortController;
    private isClosing: boolean = false;

    public get isOpen(): boolean {
        return this.dialogElement.open && !this.isClosing;
    }

    public constructor(id: string, innerHtml: string, customClassName: string[] = []) {
        const element = document.getElementById(id);
        if (element instanceof HTMLDialogElement) {
            this.dialogElement = element;
        } else {
            this.dialogElement = document.createElement("dialog");
            this.dialogElement.id = id;
            this.dialogElement.className = [
                "fixed",
                "inset-0",
                "m-0",
                "h-screen",
                "w-screen",
                "max-h-none",
                "max-w-none",
                "border-0",
                "bg-transparent",
                "p-0",
                "outline-none",
                "backdrop:bg-zinc-500/75",
                "backdrop:backdrop-blur-md",
                "backdrop:transition-opacity",
                ...HIDE_BACKDROP_CLASS_NAME,
                ...customClassName,
            ].join(" ");
            this.dialogElement.innerHTML = this.innerHtmlTemplate(innerHtml);

            document.body.appendChild(this.dialogElement);
        }

        this.modalPanel = this.dialogElement.getElementsByClassName(
            MODAL_PANEL_CLASS_NAME,
        )[0] as HTMLDivElement;

        this.closeButton = this.dialogElement.getElementsByClassName(
            CLOSE_MODAL_BUTTON_CLASS_NAME,
        )[0] as HTMLButtonElement;

        this.abortController = new AbortController();
    }

    private innerHtmlTemplate(innerHtml: string): string {
        return `
            <div class="fixed inset-0 z-10 overflow-y-auto w-screen">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <!-- Modal panel, show/hide based on modal state. -->
                    <div
                        class="${MODAL_PANEL_CLASS_NAME} relative transform overflow-hidden rounded-xl text-left transition-all sm:w-fit sm:max-w-6xl ${HIDE_MODAL_PANEL_CLASS_NAME.join(" ")}"
                    >
                        ${innerHtml}
                    </div>
                </div>
            </div>

            <div class="fixed top-10 right-10 z-10">
                <button
                    type="button"
                    class="${CLOSE_MODAL_BUTTON_CLASS_NAME} text-zinc-200 transition duration-300 hover:text-zinc-50 cursor-pointer opacity-0"
                    aria-label="關閉"
                    title="關閉"
                >
                   ${X_CIRCLE_FILL_ICON_SVG}
                </button>
            </div>
        `;
    }

    private freezeWindowScrollbar() {
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        document.documentElement.style.overflow = "hidden";
        document.documentElement.style.paddingRight = `${scrollbarWidth}px`;
    }

    private unfreezeWindowScrollbar() {
        document.documentElement.style.overflow = "";
        document.documentElement.style.paddingRight = "";
    }

    public open() {
        if (this.isOpen) {
            return;
        }

        this.isClosing = false;

        this.freezeWindowScrollbar();

        this.dialogElement.showModal();

        this.dialogElement.classList.remove(...HIDE_BACKDROP_CLASS_NAME);
        this.dialogElement.classList.add(...SHOW_BACKDROP_CLASS_NAME);
        this.modalPanel.classList.remove(...HIDE_MODAL_PANEL_CLASS_NAME);
        this.modalPanel.classList.add(...SHOW_MODAL_PANEL_CLASS_NAME);
        this.closeButton.classList.remove("opacity-0");
        this.closeButton.classList.add("opacity-100");

        this.setupCloseHandlers();
    }

    private setupCloseHandlers() {
        // Native cancel event (e.g. Escape key)
        this.dialogElement.addEventListener(
            "cancel",
            (event: Event) => {
                event.preventDefault();
                this.close();
            },
            { signal: this.abortController.signal },
        );

        // Close by clicking the backdrop area or close button
        this.dialogElement.addEventListener("click", () => this.close(), {
            signal: this.abortController.signal,
        });

        // Prevent closing when clicking modal content
        this.modalPanel.addEventListener(
            "click",
            (event: Event) => {
                event.stopPropagation();
            },
            { signal: this.abortController.signal },
        );
    }

    public close() {
        if (!this.isOpen || this.isClosing) {
            return;
        }
        this.isClosing = true;

        // Abort all event listeners
        this.abortController.abort();
        // Create a new controller for next time
        this.abortController = new AbortController();

        const finishClose = () => {
            if (this.dialogElement.open) {
                this.dialogElement.close();
            }
            this.isClosing = false;
            this.unfreezeWindowScrollbar();
        };

        this.dialogElement.classList.remove(...SHOW_BACKDROP_CLASS_NAME);
        this.dialogElement.classList.add(...HIDE_BACKDROP_CLASS_NAME);
        this.modalPanel.classList.remove(...SHOW_MODAL_PANEL_CLASS_NAME);
        this.modalPanel.classList.add(...HIDE_MODAL_PANEL_CLASS_NAME);
        this.closeButton.classList.remove("opacity-100");
        this.closeButton.classList.add("opacity-0");

        const animations = this.modalPanel.getAnimations();
        if (animations.length === 0) {
            finishClose();
        } else {
            Promise.allSettled(animations.map((animation) => animation.finished)).then(() => {
                if (this.isClosing) {
                    finishClose();
                }
            });
        }
    }

    public remove() {
        this.isClosing = false;
        if (this.dialogElement.open) {
            this.dialogElement.close();
        }
        this.unfreezeWindowScrollbar();
        this.abortController.abort();
        this.dialogElement.remove();
    }
}
