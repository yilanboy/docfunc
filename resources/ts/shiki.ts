import { createHighlighter, type Highlighter } from 'shiki';
import { languageSettings } from './config.js';

declare global {
    interface Window {
        highlightAllInElement: (element: HTMLElement) => Promise<void>;
        highlightObserver: (element: HTMLElement) => Promise<MutationObserver>;
    }
}

let highlighter: Highlighter | null = null;

async function getHighlighter(): Promise<Highlighter> {
    if (!highlighter) {
        highlighter = await createHighlighter({
            langs: Object.keys(languageSettings),
            themes: ['one-light', 'one-dark-pro'],
        });
    }

    return highlighter;
}

async function highlightElement(
    codeElement: HTMLElement,
    highlighter: Highlighter,
) {
    if (codeElement.classList.contains('shiki-highlighted')) {
        return;
    }

    const langClass = Array.from(codeElement.classList).find((c) =>
        c.startsWith('language-'),
    );
    const lang = langClass ? langClass.replace('language-', '') : 'text';
    const code = codeElement.innerText;

    try {
        const html = highlighter.codeToHtml(code, {
            lang,
            themes: {
                light: 'one-light',
                dark: 'one-dark-pro',
            },
            colorReplacements: {
                // Change background color
                'one-light': {
                    '#fafafa': '#f3f4f6',
                },
            },
        });

        // Shiki returns a <pre>...</pre> block.
        // We need to replace the parent <pre> if it exists, or just the <code> if not.
        // But the existing structure is <pre><code>...</code></pre>.
        // And code-block-helper expects that structure.

        // Let's parse the returned HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const pre = doc.querySelector('pre');

        if (pre) {
            // Copy styles and classes
            // pre.classList.add(...Array.from(codeElement.parentElement?.classList || []));
            // Actually shiki adds its own styles.

            // We want to keep the existing behavior where code-block-helper finds the pre tag.
            // The existing code-block-helper looks for 'pre code'.

            // If we replace the content of the existing pre with the content of the new pre?
            // Shiki's pre has style="background-color:..." and class="shiki ..."

            const newCode = pre.querySelector('code');
            if (newCode) {
                // We replace the innerHTML of the existing code element,
                // But we also need to apply the styles to the pre-element if we want the background

                if (
                    codeElement.parentElement &&
                    codeElement.parentElement.tagName === 'PRE'
                ) {
                    const parentPre = codeElement.parentElement;
                    parentPre.style.cssText = pre.style.cssText;
                    parentPre.classList.add('shiki');

                    // Replace the code element with the new one to keep structure
                    // Or just update innerHTML
                    codeElement.innerHTML = newCode.innerHTML;
                    codeElement.classList.add('shiki-highlighted');
                    // Add a language class back if needed, but shiki might not need it for styling
                    if (langClass) codeElement.classList.add(langClass);
                } else {
                    // Standalone code element?
                    codeElement.innerHTML = newCode.innerHTML;
                    codeElement.style.cssText = pre.style.cssText; // Apply background to code?
                    codeElement.classList.add('shiki-highlighted');
                }
            }
        }
    } catch (e) {
        console.warn(`Failed to highlight language: ${lang}`, e);
        // Fallback or ignore
    }
}

async function highlightAllInElement(htmlElement: HTMLElement): Promise<void> {
    const highlighter = await getHighlighter();

    let codeElements = htmlElement.querySelectorAll(
        'pre code:not(.shiki-highlighted)',
    ) as NodeListOf<HTMLElement>;

    for (const codeElement of codeElements) {
        await highlightElement(codeElement, highlighter);
    }
}

window.highlightAllInElement = highlightAllInElement;

async function highlightObserver(
    htmlElement: HTMLElement,
): Promise<MutationObserver> {
    const highlighter = await getHighlighter();

    let observer = new MutationObserver(async () => {
        let codeElements = htmlElement.querySelectorAll(
            'pre code:not(.shiki-highlighted)',
        ) as NodeListOf<HTMLElement>;

        for (const codeElement of codeElements) {
            await highlightElement(codeElement, highlighter);
        }
    });

    observer.observe(htmlElement, {
        childList: true,
        subtree: true,
        attributes: true,
        characterData: false,
    });

    return observer;
}

window.highlightObserver = highlightObserver;
