import {
    browserSupportsWebAuthn,
    startAuthentication,
    startRegistration,
} from '@simplewebauthn/browser';

declare global {
    interface Window {
        browserSupportsWebAuthn: Function;
        startAuthentication: Function;
        startRegistration: Function;
    }
}

window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.startAuthentication = startAuthentication;
window.startRegistration = startRegistration;
