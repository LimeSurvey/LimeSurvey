/* eslint-disable no-alert, no-console */

/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */


exports.install = function (Vue) {
    console.ls.debug(`The systen is currently in debug mode.`);


    Vue.prototype.$log = console.ls;
};
