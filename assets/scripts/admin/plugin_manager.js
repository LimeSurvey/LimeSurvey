/**
 * All JS related to plugin manager.
 */

// Namespace
var LS = LS || {
    onDocumentReady: {},
};

LS.pluginManager = (function () {
    var module = {};

    /**
     * Not used.
     * @param {string} msg
     * @return {boolean}
     */
    module.confirm = function (msg) {
        // TODO: Possible to return boolean from bootstrap modal?
        $("#confirmation-modal").data("href", "blablabla").modal("show");
        return false;
    };

    /**
     * Init this module.
     */
    module.init = function () {
        LS.doToolTip();

        let changeBlacklistButtons = document.querySelectorAll(
            ".action_changePluginStatus input"
        );
        for (let changeBlacklistButton of changeBlacklistButtons) {
            changeBlacklistButton.addEventListener("change", (event) => {
                let params =
                    "pluginId=" +
                    event.target.closest("tr").dataset.id +
                    "&YII_CSRF_TOKEN=" +
                    LS.data.csrfToken;

                let xhttp = new XMLHttpRequest();
                xhttp.open(
                    "POST",
                    "/limesurvey/master/index.php/admin/pluginmanager?sa=" +
                        (event.target.value == 1 ? "activate" : "deactivate"),
                    true
                );
                xhttp.setRequestHeader(
                    "Content-type",
                    "application/x-www-form-urlencoded"
                );
                xhttp.send(params);
            });
        }

        // Bound event to uninstall plugin button (plugin list).
        //$('.ls-pm-uninstall-plugin').on('click', module.uninstallPlugin);
    };

    return module;
})();

$(document).on("ready pjax:scriptcomplete", LS.pluginManager.init);
