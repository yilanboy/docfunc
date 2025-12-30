@props([
    'tooltipText' => 'Copy link',
    'clickText' => '',
    'tooltipPosition' => 'top',
])

@script
<script>
    Alpine.data('uiTooltip', () => ({
        tooltipVisible: false,
        tooltipText: '',
        clickText: '',
        tooltipArrow: true,
        tooltipPosition: '',

        mouseEnterTooltip() {
            this.tooltipVisible = true;
        },

        mouseLeaveTooltip() {
            this.tooltipVisible = false;
        },

        clickTooltip() {
            if (typeof this.clickText !== 'string' || this.clickText.trim().length === 0) return;

            const originalText = this.tooltipText;

            this.tooltipText = this.clickText;
            setTimeout(() => this.tooltipText = originalText, 2000);
        },

        tooltipVisibleClassListBinging: {
            [':class']() {
                return {
                    top: 'top-0 left-1/2 -translate-x-1/2 -mt-0.5 -translate-y-full',
                    left: 'top-1/2 -translate-y-1/2 -ml-0.5 left-0 -translate-x-full',
                    bottom: 'bottom-0 left-1/2 -translate-x-1/2 -mb-0.5 translate-y-full',
                    right: 'top-1/2 -translate-y-1/2 -mr-0.5 right-0 translate-x-full'
                } [this.tooltipPosition];
            }
        },

        tooltipArrowPositionClassListBinging: {
            [':class']() {
                return {
                    top: 'bottom-0 -translate-x-1/2 left-1/2 w-2.5 translate-y-full',
                    left: 'right-0 -translate-y-1/2 top-1/2 h-2.5 -mt-px translate-x-full',
                    bottom: 'top-0 -translate-x-1/2 left-1/2 w-2.5 -translate-y-full',
                    right: 'left-0 -translate-y-1/2 top-1/2 h-2.5 -mt-px -translate-x-full'
                } [this.tooltipPosition];
            }
        },

        tooltipArrowAngleClassListBinging: {
            [':class']() {
                return {
                    top: 'origin-top-left -rotate-45',
                    left: 'origin-top-left rotate-45',
                    bottom: 'origin-bottom-left rotate-45',
                    right: 'origin-top-right -rotate-45'
                } [this.tooltipPosition];
            }
        },

        init() {
            this.tooltipText = this.$root.dataset.tooltipText;
            this.clickText = this.$root.dataset.clickText;
            this.tooltipPosition = this.$root.dataset.tooltipPosition;
        }
    }));
</script>
@endscript

<div
    data-tooltip-text="{{ $tooltipText }}"
    data-click-text="{{ $clickText }}"
    data-tooltip-position="{{ $tooltipPosition }}"
    {{ $attributes->merge(['class' => 'relative w-fit h-fit']) }}
    x-data="uiTooltip"
>
    <div
        class="absolute w-auto text-sm"
        x-ref="tooltip"
        x-show="tooltipVisible"
        x-bind="tooltipVisibleClassListBinging"
        x-cloak
    >
        <div
            class="relative py-1 px-2 text-white rounded-sm bg-emerald-500/90 dark:bg-lividus-600/90"
            x-show="tooltipVisible"
            x-transition
        >
            <p
                class="block text-sm whitespace-nowrap shrink-0"
                x-text="tooltipText"
            ></p>
            <div
                class="inline-flex overflow-hidden absolute justify-center items-center"
                x-ref="tooltipArrow"
                x-show="tooltipArrow"
                x-bind="tooltipArrowPositionClassListBinging"
            >
                <div
                    class="w-1.5 h-1.5 transform bg-emerald-500/90 dark:bg-lividus-600/90"
                    x-bind="tooltipArrowAngleClassListBinging"
                ></div>
            </div>
        </div>
    </div>

    <div
        x-ref="content"
        x-on:click="clickTooltip"
        x-on:mouseenter="mouseEnterTooltip"
        x-on:mouseleave="mouseLeaveTooltip"
    >
        {{ $slot }}
    </div>
</div>
