<div
  x-cloak
  x-data="alertComponent(@js(session()->get('alert')))"
  x-init="if (alert !== null) {
      showAlert(alert.status, alert.message)

      setTimeout(function() {
          openAlertBox = false
      }, 3000);
  }"
  @info-badge.window="
    showAlert(event.detail.status, event.detail.message)

    setTimeout(function () {
      openAlertBox=false
    }, 3000);
  "
  x-show="openAlertBox"
  class="fixed bottom-0 left-0"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-300"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
>
  <div class="p-10">
    <div
      class="flex items-center rounded px-4 py-3 text-lg font-bold text-white shadow-md"
      :class="alertBackgroundColor"
      role="alert"
    >
      <span
        x-html="alertMessage"
        class="flex items-center"
      ></span>
      <button
        type="button"
        class="flex"
        @click="openAlertBox = false"
      >
        <svg
          fill="none"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="1"
          viewBox="0 0 24 24"
          stroke="currentColor"
          class="ml-4 h-4 w-4"
        >
          <path d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
  </div>
</div>
