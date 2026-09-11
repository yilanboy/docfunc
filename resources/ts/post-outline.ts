import debounce from "./debounce.js";

declare global {
    interface Window {
        setupPostOutline: (
            postOutline: HTMLElement | null,
            postBody: HTMLElement | null,
        ) => void;
    }
}

function renderPostOutline(
    postOutline: HTMLElement,
    headings: NodeListOf<HTMLHeadingElement>,
): void {
    postOutline.innerHTML = `
        <div class="flex justify-center items-center mb-4 dark:text-zinc-50" role="heading" aria-level="2">目錄</div>
        <hr class="mb-1 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700" role="separator">
    `;

    headings.forEach((heading: HTMLHeadingElement, index: number): void => {
        const headingId = heading.id || `heading-${index}`;
        heading.id = headingId;
        heading.setAttribute("tabindex", "-1");

        const link: HTMLAnchorElement = document.createElement("a");
        link.href = `#${headingId}`;
        link.id = `${headingId}-link`;
        link.className =
            "mb-1 flex items-center rounded-sm p-1 text-sm text-zinc-500 transition duration-150 " +
            "hover:bg-zinc-300 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200";

        const iconSpan = document.createElement("span");
        iconSpan.className = "flex justify-center items-center";
        iconSpan.setAttribute("aria-hidden", "true");
        iconSpan.textContent = "⏵";

        const textSpan = document.createElement("span");
        textSpan.className = "ml-2 truncate";
        textSpan.textContent = heading.textContent;

        link.appendChild(iconSpan);
        link.appendChild(textSpan);
        postOutline.appendChild(link);
    });

    postOutline.setAttribute("aria-label", "Table of contents");
    postOutline.setAttribute("role", "navigation");
}

function setupOutlineNavigation(postOutline: HTMLElement): void {
    const outlineLinks: NodeListOf<HTMLAnchorElement> =
        postOutline.querySelectorAll("a");

    outlineLinks.forEach((outlineLink: HTMLAnchorElement) => {
        outlineLink.addEventListener("click", (event: MouseEvent) => {
            const hash = outlineLink.getAttribute("href");
            if (!hash?.startsWith("#")) {
                return;
            }

            const target = document.getElementById(hash.slice(1));
            if (target) {
                event.preventDefault();
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
                history.replaceState(null, "", hash);
            }
        });
    });
}

function setupScrollSpy(
    postOutline: HTMLElement,
    postBody: HTMLElement,
    headings: NodeListOf<HTMLHeadingElement>,
): void {
    const outlineLinks: NodeListOf<HTMLAnchorElement> =
        postOutline.querySelectorAll("a");

    const clearActiveLinks = () => {
        outlineLinks.forEach((link) => {
            link.classList.remove(
                "bg-zinc-300",
                "text-zinc-900",
                "font-medium",
                "dark:bg-zinc-600",
                "dark:text-zinc-100",
            );
            link.removeAttribute("aria-current");
        });
    };

    const updateActiveSection = () => {
        const postRect = postBody.getBoundingClientRect();

        // If user hasn't scrolled down to post yet or scrolled past the article
        if (postRect.top > window.innerHeight * 0.5 || postRect.bottom < 50) {
            clearActiveLinks();
            return;
        }

        // When scrolled near the bottom of page, highlight the last section
        const isAtBottom =
            window.innerHeight + window.scrollY >=
            document.documentElement.scrollHeight - 50;

        let activeHeading: HTMLHeadingElement | null = null;

        if (isAtBottom) {
            activeHeading = headings[headings.length - 1];
        } else {
            const activationOffset = 120;
            for (let i = headings.length - 1; i >= 0; i--) {
                const heading = headings[i];
                if (heading.getBoundingClientRect().top <= activationOffset) {
                    activeHeading = heading;
                    break;
                }
            }

            // If none crossed activationOffset yet but reader is inside article, default to first heading
            if (!activeHeading && headings.length > 0) {
                activeHeading = headings[0];
            }
        }

        clearActiveLinks();

        if (activeHeading) {
            const activeLink = document.getElementById(
                `${activeHeading.id}-link`,
            );
            if (activeLink) {
                activeLink.classList.add(
                    "bg-zinc-300",
                    "text-zinc-900",
                    "font-medium",
                    "dark:bg-zinc-600",
                    "dark:text-zinc-100",
                );
                activeLink.setAttribute("aria-current", "location");
            }
        }
    };

    const debouncedUpdate = debounce(updateActiveSection, 50);

    const resizeObserver = new ResizeObserver(() => {
        updateActiveSection();
    });

    resizeObserver.observe(postBody);

    window.addEventListener("scroll", debouncedUpdate, { passive: true });
    window.addEventListener("resize", debouncedUpdate, { passive: true });

    // Initial check
    updateActiveSection();

    document.addEventListener(
        "livewire:navigating",
        () => {
            resizeObserver.disconnect();
            window.removeEventListener("scroll", debouncedUpdate);
            window.removeEventListener("resize", debouncedUpdate);
        },
        { once: true },
    );
}

window.setupPostOutline = function (
    postOutline: HTMLElement | null,
    postBody: HTMLElement | null,
): void {
    if (!postOutline || !postBody) {
        return;
    }

    const headings: NodeListOf<HTMLHeadingElement> =
        postBody.querySelectorAll("h2");

    if (headings.length === 0) {
        return;
    }

    renderPostOutline(postOutline, headings);
    setupOutlineNavigation(postOutline);
    setupScrollSpy(postOutline, postBody, headings);
};
