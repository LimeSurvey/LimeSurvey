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

    /**
     * Fetch HTML and put it in limestore tab
     *
     * @param {string} url
     */
    module.populateLimestoreList = function(url) {
        $('#nav-tab-limestore').prop('onclick', null);
        $.get(
            url,
            {},
            function(data) {
                $('#tab-limestore').html(data);
                $('#limestore-grid').yiiGridView({'ajaxUpdate':['limestore\x2Dgrid'],'ajaxVar':'ajax','pagerClass':'pager','loadingClass':'grid\x2Dview\x2Dloading','filterClass':'filters','tableClass':'items\x20table\x20table\x2Dhover','selectableRows':1,'enableHistory':false,'updateSelector':'\x7Bpage\x7D,\x20\x7Bsort\x7D','filterSelector':'\x7Bfilter\x7D','pageVar':'page'});
            }
        );
    };

    return module;
})();

$(document).on('ready pjax:scriptcomplete', LS.pluginManager.init);
