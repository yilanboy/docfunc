<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('alertComponent', () => ({
      AlertBoxIsOpen: false,
      alertBackgroundColor: '',
      alertMessage: '',
      successIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 mr-2 text-white"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
      infoIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 mr-2 text-white"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
      warningIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 mr-2 text-white"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
      dangerIcon: `<svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 mr-2 text-white"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>`,

      showAlert(status, message) {
        switch (status) {
          case 'success':
            this.alertBackgroundColor = 'bg-emerald-500';
            this.alertMessage = `${this.successIcon} ${message}`;
            break;
          case 'info':
            this.alertBackgroundColor = 'bg-blue-500';
            this.alertMessage = `${this.infoIcon} ${message}`;
            break;
          case 'warning':
            this.alertBackgroundColor = 'bg-yellow-500';
            this.alertMessage = `${this.warningIcon} ${message}`;
            break;
          case 'danger':
            this.alertBackgroundColor = 'bg-red-500';
            this.alertMessage = `${this.dangerIcon} ${message}`;
            break;
        }

        this.AlertBoxIsOpen = true;
      },

      AlertDispatched(event) {
        this.showAlert(event.detail.status, event.detail.message)

        setTimeout(() => {
          this.AlertBoxIsOpen = false
        }, 3000);
      },

      closeAlertBox() {
        this.AlertBoxIsOpen = false;
      }
    }));
  })
</script>

<div
  class="fixed bottom-0 left-0"
  x-cloak
  x-data="alertComponent"
  x-on:info-badge.window="AlertDispatched"
  x-show="AlertBoxIsOpen"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-300"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
>
  <div class="p-10">
    <div
      class="flex items-center rounded-sm px-4 py-3 text-lg text-white"
      role="alert"
      :class="alertBackgroundColor"
    >
      <span
        class="flex items-center"
        x-html="alertMessage"
      ></span>
      <button
        class="flex"
        type="button"
        x-on:click="closeAlertBox"
      >
        <x-icon.x class="size-6" />
      </button>
    </div>
  </div>
</div>
