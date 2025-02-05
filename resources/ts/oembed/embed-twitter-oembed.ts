declare global {
    interface Window {
        processTwitterOembeds: Function;
        twttr: any;
    }
}

// 定義一個函式來處理 X (舊稱 Twitter) oembed 轉換
async function convertTwitterOembedToIframe(
    oembedElement: HTMLElement,
): Promise<void> {
    const csrfMeta: HTMLMetaElement | null = document.querySelector(
        'meta[name="csrf-token"]',
    );

    let theme: string = 'light';
    if (document.documentElement.classList.contains('dark')) {
        theme = 'dark';
    }

    const url = oembedElement.getAttribute('url');
    if (!url || !isTwitterUrl(url)) {
        return;
    }

    const response = await fetch('/api/oembed/twitter', {
        method: 'POST',
        body: JSON.stringify({ url: url, theme: theme }),
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'x-csrf-token': csrfMeta ? csrfMeta.content : '',
        },
    });

    const data = await response.json();

    if (data.html) {
        oembedElement.insertAdjacentHTML('afterend', data.html);
        oembedElement.classList.add('oembed-processed');
    }
}

// 定義一個函式來檢查是否為 Twitter 連結
// 目前 ckeditor 還是只支援 twitter.com 的連結，尚未支援 x.com
function isTwitterUrl(url: string): boolean {
    return /^https?:\/\/(www\.)?(twitter|x)\.com\/[^/]+\/status\/\d+/.test(url);
}

// source code :
// https://developer.twitter.com/en/docs/twitter-for-websites/javascript-api/guides/set-up-twitter-for-websites
window.twttr = (function (d, s, id) {
    let js;
    let fjs = d.getElementsByTagName(s)[0];
    let t = window.twttr || {};

    if (d.getElementById(id)) return t;

    js = <HTMLScriptElement>d.createElement(s);
    js.id = id;
    js.src = 'https://platform.twitter.com/widgets.js';

    if (fjs.parentNode !== null) fjs.parentNode.insertBefore(js, fjs);

    t._e = [];
    t.ready = function (f: any) {
        t._e.push(f);
    };
    return t;
})(document, 'script', 'twitter-wjs');

window.processTwitterOembeds = function (postBody: HTMLElement): void {
    const oembedElements: NodeListOf<HTMLElement> = document.querySelectorAll(
        'oembed:not(.oembed-processed)',
    );

    oembedElements.forEach((oembedElement) => {
        const figureElement = oembedElement.closest('figure.media');

        if (figureElement) {
            convertTwitterOembedToIframe(oembedElement).catch((error) => {
                console.log(
                    'Error on convert X (The old name was Twitter.) oembed:',
                    error,
                );
            });
        }
    });

    setTimeout(() => {
        window.twttr.widgets?.load(postBody);
    }, 1000);
};
