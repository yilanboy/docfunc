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
    preElement: HTMLPreElement,
    highlighter: Highlighter,
) {
    if (preElement.classList.contains('shiki-highlighted')) {
        return;
    }

    const codeElement = preElement.querySelector('code');

    if (!codeElement) {
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

        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const pre = template.content.firstChild as HTMLElement;

        preElement.replaceWith(pre);
        pre.classList.add('shiki-highlighted');
    } catch (e) {
        console.warn(`Failed to highlight language: ${lang}`, e);
        // Fallback or ignore
    }
}

async function highlightAllInElement(htmlElement: HTMLElement): Promise<void> {
    const highlighter = await getHighlighter();

    let preElements = htmlElement.querySelectorAll(
        'pre:not(.shiki-highlighted)',
    ) as NodeListOf<HTMLPreElement>;

    for (const preElement of preElements) {
        await highlightElement(preElement, highlighter);
    }
}

window.highlightAllInElement = highlightAllInElement;

async function highlightObserver(
    htmlElement: HTMLElement,
): Promise<MutationObserver> {
    const highlighter = await getHighlighter();

    let observer = new MutationObserver(async () => {
        let preElements = htmlElement.querySelectorAll(
            'pre:not(.shiki-highlighted)',
        ) as NodeListOf<HTMLPreElement>;

        for (const preElement of preElements) {
            await highlightElement(preElement, highlighter);
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
