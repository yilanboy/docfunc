declare global {
    interface Window {
        setupProgressBar: (section: HTMLElement, progressBar: HTMLElement) => void;
    }
}

function updateProgress(section: HTMLElement, progressBar: HTMLElement): void {
    const rect = section.getBoundingClientRect();
    const scrollable = rect.height - document.documentElement.clientHeight;
    const raw = scrollable > 0 ? (-rect.top / scrollable) * 100 : 100;
    const value = Math.max(0, Math.min(100, Math.floor(raw) || 0));

    progressBar.style.width = `${value}%`;
    progressBar.ariaValueNow = String(value);
}

window.setupProgressBar = function (section, progressBar) {
    let scrollAttached = false;
    const onScroll = () => updateProgress(section, progressBar);

    const sync = () => {
        const sectionHeight = section.getBoundingClientRect().height;
        if (sectionHeight <= 0) return;

        if (document.documentElement.clientHeight >= sectionHeight) {
            progressBar.style.width = '100%';
            progressBar.ariaValueNow = '100';
            if (scrollAttached) {
                document.removeEventListener('scroll', onScroll);
                scrollAttached = false;
            }
            return;
        }

        if (!scrollAttached) {
            document.addEventListener('scroll', onScroll, { passive: true });
            scrollAttached = true;
        }
        updateProgress(section, progressBar);
    };

    const observer = new ResizeObserver(sync);
    observer.observe(section);
    window.addEventListener('resize', sync);

    document.addEventListener('livewire:navigating', () => {
        observer.disconnect();
        window.removeEventListener('resize', sync);
        if (scrollAttached) {
            document.removeEventListener('scroll', onScroll);
        }
    }, { once: true });
};
