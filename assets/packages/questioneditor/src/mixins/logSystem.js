/* eslint-disable no-alert, no-console */

/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */


exports.install = function (Vue) {
    if(window.debugState.backend) {
        Vue.prototype.$log = console.ls;
    } else {
        Vue.prototype.$log = console.ls.silent;
    }
};
