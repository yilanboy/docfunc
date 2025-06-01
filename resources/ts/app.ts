interface LivewireDirective {
    el: HTMLElement;
    component: {
        $wire: {
            $hook: Function;
        };
    };
    cleanup: Function;
}

document.addEventListener('livewire:init', () => {
    // @ts-ignore
    Livewire.directive(
        'retain',
        ({ el, component, cleanup }: LivewireDirective) => {
            function onClick() {
                let currentScrollY = 0;

                component.$wire.$hook('commit.prepare', () => {
                    currentScrollY = window.scrollY;
                });

                component.$wire.$hook('morph.updated', () => {
                    // make sure scroll position will update after dom updated
                    queueMicrotask(() => {
                        window.scrollTo({
                            top: currentScrollY,
                            behavior: 'instant',
                        });
                    });
                });
            }

            el.addEventListener('click', onClick, {
                capture: true,
            });

            cleanup(() => {
                el.removeEventListener('click', onClick);
            });
        },
    );
});
