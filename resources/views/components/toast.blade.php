<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('uiToast', () => ({
            isShow: false,
            backgroundColor: '',
            message: '',
            successIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="mr-2 w-6 h-6 text-white"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            infoIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="mr-2 w-6 h-6 text-white"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            warningIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="mr-2 w-6 h-6 text-white"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            dangerIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="mr-2 w-6 h-6 text-white"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>`,

            showToast(status, message) {
                switch (status) {
                    case 'success':
                        this.backgroundColor = 'bg-emerald-500';
                        this.message = `${this.successIcon} ${message}`;
                        break;
                    case 'info':
                        this.backgroundColor = 'bg-blue-500';
                        this.message = `${this.infoIcon} ${message}`;
                        break;
                    case 'warning':
                        this.backgroundColor = 'bg-yellow-500';
                        this.message = `${this.warningIcon} ${message}`;
                        break;
                    case 'danger':
                        this.backgroundColor = 'bg-red-500';
                        this.message = `${this.dangerIcon} ${message}`;
                        break;
                }

                this.isShow = true;
            },

            toastDispatched(event) {
                this.showToast(event.detail.status, event.detail.message);

                setTimeout(() => {
                    this.isShow = false;
                }, 6000);
            },

            closeToast() {
                this.isShow = false;
            }
        }));
    });
</script>

<div
    class="fixed bottom-0 left-0"
    x-cloak
    x-data="uiToast"
    x-on:toast.window="toastDispatched"
    x-show="isShow"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="p-10">
        <div
            class="flex items-center py-3 px-4 text-lg text-white rounded-sm"
            role="alert"
            :class="backgroundColor"
        >
      <span
          class="flex items-center"
          x-html="message"
      ></span>
            <button
                class="flex"
                type="button"
                x-on:click="closeToast"
            >
                <x-icons.x class="size-6" />
            </button>
        </div>
    </div>
</div>
