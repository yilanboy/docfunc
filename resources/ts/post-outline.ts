import debounce from './debounce.js';

declare global {
    interface Window {
        setupPostOutline: Function;
    }
}

function createPostOutlineLinks(
    postOutline: HTMLElement,
    postBody: HTMLElement,
): void {
    let headings: NodeListOf<HTMLHeadingElement> =
        postBody.querySelectorAll('h2');

    if (headings.length === 0) {
        return;
    }

    let postOutlineInnerHtml: string = '';

    postOutlineInnerHtml += `
        <div class="mb-4 flex items-center justify-center dark:text-zinc-50">目錄</div>
        <hr class="mb-1 h-0.5 border-0 bg-zinc-300 dark:bg-zinc-700">
    `;

    headings.forEach((heading: HTMLHeadingElement, index: number): void => {
        heading.id = `heading-${index}`;

        postOutlineInnerHtml += `
            <a
                href="#${heading.id}"
                id="${heading.id}-link"
                class="mb-1 flex rounded-sm p-1 text-sm text-zinc-500 transition duration-150 hover:bg-zinc-300 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
            >
                <span class="flex items-center justify-center">⏵</span>
                <span class="ml-2">${heading.textContent}</span>
            </a>
        `;
    });

    postOutline.innerHTML = postOutlineInnerHtml;
}

function addClickEventOnPostLinks(postOutline: HTMLElement) {
    let outlineLinks: NodeListOf<HTMLAnchorElement> =
        postOutline.querySelectorAll('a');

    outlineLinks.forEach((outlineLink: HTMLAnchorElement, index: number) => {
        let section: Element | null = document.getElementById(
            `heading-${index}-section`,
        );

        if (section === null) {
            return;
        }

        outlineLink.addEventListener('click', (event) => {
            event.preventDefault();
            section?.scrollIntoView({
                behavior: 'smooth',
            });
        });
    });
}

function showWhichSectionIAmIn(
    postOutline: HTMLElement,
    postBody: HTMLElement,
): void {
    const outlineLinks: NodeListOf<HTMLAnchorElement> =
        postOutline.querySelectorAll('a');
    let headingScrollYs: Record<string, number> = {};

    const updateHeadingScrollYs = () => {
        const headings = postBody.querySelectorAll('h2');
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
    createPostOutlineLinks(postOutline, postBody);
    addClickEventOnPostLinks(postOutline);
    // Must be after createSectionInPostBdy
    showWhichSectionIAmIn(postOutline, postBody);
};
