declare global {
    interface Window {
        setupScrollToTopButton: (button: HTMLButtonElement) => void;
    }
}

// 滾動至網頁最頂部
function scrollToTop(): void {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

window.setupScrollToTopButton = function (
    scrollToTopButton: HTMLButtonElement,
): void {
    scrollToTopButton.addEventListener('click', scrollToTop);

    const header = document.getElementById('header');
    const footer = document.getElementById('footer');

    // 根據 header / footer 是否出現在畫面上調整按鈕的樣式
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.target === header) {
                    if (entry.isIntersecting) {
                        // header 在畫面上
                        scrollToTopButton.classList.remove('xl:flex');
                    } else {
                        // header 不在畫面上
                        scrollToTopButton.classList.add('xl:flex');
                    }
                } else if (entry.target === footer) {
                    if (entry.isIntersecting) {
                        // footer 在畫面上
                        scrollToTopButton.classList.remove('fixed', 'bottom-7');
                        scrollToTopButton.classList.add('absolute', 'bottom-1');
                    } else {
                        // footer 不在畫面上
                        scrollToTopButton.classList.add('fixed', 'bottom-7');
                        scrollToTopButton.classList.remove('absolute', 'bottom-1');
                    }
                }
            }
        },
        { threshold: [0] },
    );

    if (header) {
        observer.observe(header);
    }
    if (footer) {
        observer.observe(footer);
    }

    document.addEventListener(
        'livewire:navigating',
        () => {
            observer.disconnect();
            scrollToTopButton.removeEventListener('click', scrollToTop);
        },
        { once: true },
    );
};
