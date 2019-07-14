/* eslint-disable no-alert, no-console */

/**
 * Check the browsers console capabilities and bundle them into general functions
 * If the build environment was "production" only put out error messages.
 */
import ConsoleShim from '../../../meta/lib/ConsoleShim.js';

const LOG = new ConsoleShim('adminsidepanel');

if(!window.debugState.backend) {
    LOG.setSilent(true);
}

const PluginLog = function (Vue) {
    Vue.prototype.$log = LOG;
};

export {PluginLog, LOG};
