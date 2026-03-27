// global frontend logic here
import debounce from './debounce.js';

declare global {
    interface Window {
        debounce: typeof debounce;
    }
}

window.debounce = debounce;
