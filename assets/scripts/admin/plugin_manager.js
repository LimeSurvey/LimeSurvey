/**
 * All JS related to plugin manager.
 */

// Namespace
var LS = LS || {
    onDocumentReady: {}
};

LS.pluginManager = (function() {
    var module = {};

    /**
     * Not used.
     * @param {string} msg
     * @return {boolean}
     */
    module.confirm = function(msg) {
        // TODO: Possible to return boolean from bootstrap modal?
        $('#confirmation-modal').data('href', 'blablabla').modal('show');
        return false;
    };

    /**
     * Init this module.
     */
    module.init = function() {
        LS.doToolTip();
        // Bound event to uninstall plugin button (plugin list).
        //$('.ls-pm-uninstall-plugin').on('click', module.uninstallPlugin);
    };

    return module;
})();

$(document).on('ready pjax:scriptcomplete', LS.pluginManager.init);
