import debounce from './debounce.js';

declare global {
    interface Window {
        setupPostOutline: (
            postOutline: HTMLElement,
            postBody: HTMLElement,
        ) => void;
    }
}

function createPostOutlineLinks(
    postOutline: HTMLElement,
    headings: NodeListOf<HTMLHeadingElement>,
): void {
    postOutline.innerHTML = `
        <div class="mb-4 flex items-center justify-center dark:text-zinc-50" role="heading" aria-level="2">目錄</div>
        <hr class="mb-1 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700" role="separator">
    `;

    headings.forEach((heading: HTMLHeadingElement, index: number): void => {
        heading.id = `heading-${index}`;
        heading.setAttribute('tabindex', '-1');

        const link: HTMLAnchorElement = document.createElement('a');
        link.href = `#${heading.id}`;
        link.id = `${heading.id}-link`;
        link.classList.add(
            'mb-1',
            'flex',
            'rounded-sm',
            'p-1',
            'text-sm',
            'text-zinc-500',
            'transition',
            'duration-150',
            'hover:bg-zinc-300',
            'hover:text-zinc-800',
            'dark:text-zinc-400',
            'dark:hover:bg-zinc-700',
            'dark:hover:text-zinc-200',
        );
        link.setAttribute('role', 'link');
        link.setAttribute(
            'aria-label',
            'Jump to section: ' + heading.textContent,
        );
        link.setAttribute('tabindex', '0');

        link.innerHTML = `
            <span class="flex items-center justify-center" aria-hidden="true">⏵</span>
            <span class="ml-2">${heading.textContent}</span>
        `;

        postOutline.appendChild(link);
    });

    postOutline.setAttribute('aria-label', 'Table of contents');
    postOutline.setAttribute('role', 'navigation');
}

function addClickEventOnPostLinks(postOutline: HTMLElement) {
    let outlineLinks: NodeListOf<HTMLAnchorElement> =
        postOutline.querySelectorAll('a');

    outlineLinks.forEach((outlineLink: HTMLAnchorElement, index: number) => {
        let heading: HTMLElement | null = document.getElementById(
            `heading-${index}`,
        );

        if (!heading) {
            console.warn(`Heading with id 'heading-${index}' not found`);

            return;
        }

        const handleNavigation = (event: Event) => {
            event.preventDefault();
            heading.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        };

        outlineLink.addEventListener('click', handleNavigation);
    });
}

function showWhichSectionIAmIn(
    postOutline: HTMLElement,
    postBody: HTMLElement,
    headings: NodeListOf<HTMLHeadingElement>,
): void {
    let outlineLinks: NodeListOf<HTMLAnchorElement> =
        postOutline.querySelectorAll('a');

    let headingScrollYs: Record<string, number> = {};

    const updateHeadingScrollYs = () => {
        headings.forEach((heading) => {
            headingScrollYs[heading.id] = heading.offsetTop;
        });
    };

    const resizeObserver = new ResizeObserver(() => {
        updateHeadingScrollYs();
    });

    resizeObserver.observe(postBody);
    updateHeadingScrollYs(); // Initial update

    const clearHighlighting = () => {
        outlineLinks.forEach((link) => {
            link.classList.remove('bg-zinc-300', 'dark:bg-zinc-600');
            link.setAttribute('aria-current', 'false');
        });
    };

    const highlightCurrentSection = () => {
        const currentScrollY = window.scrollY;
        const headingKeys = Object.keys(headingScrollYs).sort(
            (a, b) => headingScrollYs[a] - headingScrollYs[b],
        );

        for (let i = 0; i < headingKeys.length; i++) {
            const currentKey = headingKeys[i];
            const nextKey = headingKeys[i + 1];

            if (
                currentScrollY >= headingScrollYs[currentKey] &&
                (!nextKey || currentScrollY < headingScrollYs[nextKey]) &&
                currentScrollY <
                    postBody.getBoundingClientRect().bottom + currentScrollY
            ) {
                const outlineLink = document.getElementById(
                    `${currentKey}-link`,
                );
                outlineLink?.classList.add('bg-zinc-300', 'dark:bg-zinc-600');

                break;
            }
        }
    };

    const updateSection = debounce(() => {
        clearHighlighting();
        highlightCurrentSection();
    }, 100);

    window.addEventListener('scroll', updateSection);

    function clearPostOutlineObserverAndEvent() {
        resizeObserver.disconnect();
        window.removeEventListener('scroll', updateSection);
        window.removeEventListener(
            'livewire:navigating',
            clearPostOutlineObserverAndEvent,
        );
    }

    window.addEventListener(
        'livewire:navigating',
        clearPostOutlineObserverAndEvent,
    );
}

window.setupPostOutline = function (
    postOutline: HTMLElement,
    postBody: HTMLElement,
): void {
    // Cache headings query to avoid repeated DOM queries across functions
    const headings: NodeListOf<HTMLHeadingElement> =
        postBody.querySelectorAll('h2');

    if (headings.length === 0) {
        console.warn('No headings found in post body');

        return;
    }

    createPostOutlineLinks(postOutline, headings);

    addClickEventOnPostLinks(postOutline);
    // Must be after createSectionInPostBdy
    showWhichSectionIAmIn(postOutline, postBody, headings);
};
