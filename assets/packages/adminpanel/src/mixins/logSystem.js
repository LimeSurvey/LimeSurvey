/* eslint-disable no-alert, no-console */

/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */


exports.install = function (Vue) {
    if(window.debugState.backend) {
        console.ls.trace('The systen is currently in debug mode.');
        Vue.prototype.$log = console.ls;
    } else {
        console.log('The systen is currently in production mode.');
        Vue.prototype.$log = console.ls.silent;
    }
};
