declare global {
    interface Window {
        setupProgressBar: Function;
    }
}

function progressBarAnimation(
    section: HTMLElement,
    progressBar: HTMLElement,
): void {
    let scrollDistance: number = -section.getBoundingClientRect().top;
    let progressWidth: number =
        (scrollDistance /
            (section.getBoundingClientRect().height -
                document.documentElement.clientHeight)) *
        100;

    let value: number = Math.floor(progressWidth);

    progressBar.style.width = value + '%';
    progressBar.ariaValueNow = value.toString();

    if (value < 0) {
        progressBar.style.width = '0%';
        progressBar.ariaValueNow = '0';
    }

    if (value > 100) {
        progressBar.style.width = '100%';
        progressBar.ariaValueNow = '100';
    }
}

window.setupProgressBar = function (
    section: HTMLElement,
    progressBar: HTMLElement,
): void {
    if (
        document.documentElement.clientHeight >
        section.getBoundingClientRect().height
    ) {
        progressBar.style.width = '100%';
        progressBar.ariaValueNow = '100';

        return;
    }

    const updateProgressBar = () => progressBarAnimation(section, progressBar);

    window.addEventListener('scroll', updateProgressBar);

    function clearProgressBarEvent() {
        window.removeEventListener('scroll', updateProgressBar);
        window.removeEventListener(
            'livewire:navigating',
            clearProgressBarEvent,
        );
    }

    window.addEventListener('livewire:navigating', clearProgressBarEvent);
};
