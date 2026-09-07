import { Modal } from '../modal.js';
import { button, icon, label, languageSettings } from '../config.js';

declare global {
    interface Window {
        codeBlockHelper: (element: HTMLElement) => void;
    }
}

const ZOOM_IN_PRE_MODAL_ID = "zoom-in-pre-modal";
const ZOOM_IN_PRE_ID = "zoom-in-pre";
const SCROLL_INDICATOR_LEFT_CLASS = "scroll-indicator-left";
const SCROLL_INDICATOR_RIGHT_CLASS = "scroll-indicator-right";

let zoomInModal: Modal | null = null;

const indicatorMap = new WeakMap<
    HTMLPreElement,
    { left: HTMLDivElement; right: HTMLDivElement }
>();

let sharedResizeObserver: ResizeObserver | null = null;

function getSharedResizeObserver(): ResizeObserver {
    if (!sharedResizeObserver) {
        sharedResizeObserver = new ResizeObserver((entries) => {
            for (const entry of entries) {
                const preTag = entry.target as HTMLPreElement;
                const indicators = indicatorMap.get(preTag);
                if (indicators) {
                    updateScrollIndicators(preTag, indicators.left, indicators.right);
                }
            }
        });
    }

    return sharedResizeObserver;
}

function createCopyCodeButton(codeElement: HTMLElement): HTMLButtonElement {
    // create a copy button
    const copyButton: HTMLButtonElement = document.createElement("button");
    copyButton.type = "button";
    copyButton.setAttribute("aria-label", "複製程式碼");
    copyButton.title = "複製程式碼";
    // set button position
    copyButton.classList.add(...button.BASE_CLASS_NAME);
    copyButton.innerHTML = icon.CLIPBOARD;

    let resetTimer: ReturnType<typeof setTimeout> | null = null;

    // when the copy button is clicked, copy code to the clipboard
    copyButton.addEventListener("click", function (this: HTMLButtonElement) {
        if (!navigator.clipboard) {
            console.warn("Clipboard API not supported in current environment");
            return;
        }

        const code = codeElement.innerText;

        // copy code to clipboard
        navigator.clipboard.writeText(code).then(
            () => {
                if (resetTimer !== null) {
                    clearTimeout(resetTimer);
                }

                // change the button icon to "Copied!" for 2 seconds
                this.innerHTML = icon.CHECK;
                this.setAttribute("aria-label", "已複製！");
                this.title = "已複製！";
                resetTimer = setTimeout(() => {
                    this.innerHTML = icon.CLIPBOARD;
                    this.setAttribute("aria-label", "複製程式碼");
                    this.title = "複製程式碼";
                    resetTimer = null;
                }, 2000);
            },
            (err) => console.error("Failed to copy to clipboard", err),
        );
    });

    return copyButton;
}

function createExpandCodeButton(modal: Modal, getPreOuterHTML: () => string): HTMLButtonElement {
    const expandCodeButton: HTMLButtonElement = document.createElement("button");
    expandCodeButton.type = "button";
    expandCodeButton.setAttribute("aria-label", "放大檢視程式碼");
    expandCodeButton.title = "放大檢視";
    expandCodeButton.classList.add(...button.BASE_CLASS_NAME);
    expandCodeButton.innerHTML = icon.ARROWS_ANGLE_EXPAND;

    const zoomInCode = document.getElementById(ZOOM_IN_PRE_ID) as HTMLDivElement;

    expandCodeButton.addEventListener("click", function (this: HTMLButtonElement) {
        zoomInCode.innerHTML = getPreOuterHTML();
        modal.open();
    });

    return expandCodeButton;
}

function getProgramLanguage(element: HTMLPreElement) {
    const foundClass = element.getAttribute("data-program-language");

    if (!foundClass) {
        return "text";
    }

    return foundClass;
}

function createScrollIndicator(side: "left" | "right"): HTMLDivElement {
    const indicator = document.createElement("div");
    indicator.classList.add(
        "absolute",
        "top-0",
        "bottom-0",
        side === "right" ? "right-0" : "left-0",
        side === "right" ? SCROLL_INDICATOR_RIGHT_CLASS : SCROLL_INDICATOR_LEFT_CLASS,
        "w-12",
        "pointer-events-none",
        "transition-opacity",
        "duration-300",
    );
    indicator.style.opacity = "0";

    return indicator;
}

function updateScrollIndicators(
    preTag: HTMLPreElement,
    leftIndicator: HTMLDivElement,
    rightIndicator: HTMLDivElement,
): void {
    const { scrollLeft, scrollWidth, clientWidth } = preTag;
    const nextLeft = scrollLeft > 0 ? "1" : "0";
    // subtract 1 to absorb sub-pixel rounding when scrolled to the end
    const nextRight = scrollLeft + clientWidth < scrollWidth - 1 ? "1" : "0";
    if (leftIndicator.style.opacity !== nextLeft) leftIndicator.style.opacity = nextLeft;
    if (rightIndicator.style.opacity !== nextRight) rightIndicator.style.opacity = nextRight;
}

// create language label
function createLanguageLabel(language: string): HTMLSpanElement {
    const labelElement: HTMLSpanElement = document.createElement("span");
    labelElement.classList.add("language-label", ...label.BASE_CLASS_NAME);

    if (languageSettings[language]) {
        labelElement.innerText = languageSettings[language].label;
        labelElement.style.backgroundColor = languageSettings[language].backgroundColor;
        labelElement.style.color = languageSettings[language].color;
    } else {
        labelElement.innerText = language;
    }

    return labelElement;
}

window.codeBlockHelper = function (element: HTMLElement): void {
    const preTags: HTMLCollectionOf<HTMLPreElement> = element.getElementsByTagName("pre");

    if (preTags.length === 0) {
        return;
    }

    if (!zoomInModal) {
        const zoomInCode: HTMLDivElement = document.createElement("div");
        zoomInCode.classList.add(
            "lg:min-w-3xl",
            "max-h-[85vh]",
            "max-w-[90vw]",
            "overflow-auto",
            "rounded-xl",
        );
        zoomInCode.id = ZOOM_IN_PRE_ID;

        zoomInModal = new Modal(ZOOM_IN_PRE_MODAL_ID, zoomInCode.outerHTML);

        document.addEventListener(
            "livewire:navigating",
            () => {
                zoomInModal?.remove();
                zoomInModal = null;
            },
            { once: true },
        );
    }

    const modal = zoomInModal;
    const marker = "code-block-helper-added";
    const observer = getSharedResizeObserver();
    const cleanups: Array<() => void> = [];

    // add a code block helper to all pre-tags
    for (const preTag of preTags) {
        if (preTag.classList.contains(marker)) {
            continue;
        }

        const codes = preTag.getElementsByTagName("code");

        if (codes.length === 0) {
            continue;
        }

        const code: HTMLElement = codes[0];

        if (code.classList.contains("language-mermaid")) {
            continue;
        }

        // to make the copy button fixed in the container, we wrap it in the container
        let wrapper: HTMLDivElement = document.createElement("div");
        // add 'relative' to make this element to become an anchor
        wrapper.classList.add("group", "relative", "-mx-4");

        // set the wrapper as sibling of the pre-tag
        preTag.parentNode?.insertBefore(wrapper, preTag);
        // set element as child of wrapper
        wrapper.appendChild(preTag);

        preTag.classList.add(marker);

        // to get language from code class name, the class name is like "language-JavaScript"
        // we need to get the last part of the class name
        const language = getProgramLanguage(preTag);

        const languageLabelElement: HTMLSpanElement = createLanguageLabel(language);
        languageLabelElement.classList.add("hidden", "sm:flex");

        // start to create the copy button...
        const copyButton: HTMLButtonElement = createCopyCodeButton(code);

        const expandCodeButton = createExpandCodeButton(modal, () => preTag.outerHTML);
        expandCodeButton.classList.add("hidden", "sm:flex");

        wrapper.style.setProperty("--pre-light-bg", preTag.style.backgroundColor);
        wrapper.style.setProperty(
            "--pre-dark-bg",
            preTag.style.getPropertyValue("--shiki-dark-bg").trim(),
        );

        const leftScrollIndicator = createScrollIndicator("left");
        const rightScrollIndicator = createScrollIndicator("right");

        wrapper.appendChild(leftScrollIndicator);
        wrapper.appendChild(rightScrollIndicator);

        indicatorMap.set(preTag, {
            left: leftScrollIndicator,
            right: rightScrollIndicator,
        });
        observer.observe(preTag);

        updateScrollIndicators(preTag, leftScrollIndicator, rightScrollIndicator);

        const onScroll = () =>
            updateScrollIndicators(preTag, leftScrollIndicator, rightScrollIndicator);
        preTag.addEventListener("scroll", onScroll, { passive: true });

        const codeHelperGroup: HTMLDivElement = document.createElement("div");
        codeHelperGroup.classList.add(
            "flex",
            "gap-2",
            "absolute",
            "top-2",
            "right-2",
            "opacity-100",
            "lg:opacity-0",
            "lg:group-hover:opacity-100",
            "focus-within:opacity-100",
            "transition-opacity",
            "duration-200",
        );

        wrapper.appendChild(codeHelperGroup);

        // append these buttons in the pre-tag
        // appended language label
        codeHelperGroup.appendChild(languageLabelElement);
        codeHelperGroup.appendChild(copyButton);
        codeHelperGroup.appendChild(expandCodeButton);

        cleanups.push(() => {
            observer.unobserve(preTag);
            preTag.removeEventListener("scroll", onScroll);
            wrapper.replaceWith(preTag);
            preTag.classList.remove(marker);
        });
    }

    if (cleanups.length > 0) {
        document.addEventListener(
            "livewire:navigating",
            () => {
                sharedResizeObserver?.disconnect();
                sharedResizeObserver = null;
                for (const cleanup of cleanups) {
                    cleanup();
                }
            },
            { once: true },
        );
    }
};
