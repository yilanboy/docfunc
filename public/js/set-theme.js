/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};
/*!***********************************!*\
  !*** ./resources/ts/set-theme.ts ***!
  \***********************************/


if (localStorage.theme === 'light' || !('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches) {
  document.documentElement.classList.remove('dark');
} else {
  document.documentElement.classList.add('dark');
}
/******/ })()
;