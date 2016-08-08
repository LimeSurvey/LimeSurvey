/**
 * This file is included ONLY if user tried
 * to pay for an order. This is to update the
 * Cint order status at each page reload using
 * Ajax, so the page load won't be slower.
 *
 * @since 2016-08-05
 */

var LS = LS || {};
$(document).ready(function() {
    console.log('checkOrders');
    $.ajax({
        url: LS.plugin.cintlink.pluginBaseUrl + '&method=updateAllOrders',
        surveyId: LS.plugin.cintlink.surveyId,
        success: function(data) {
            console.log('data', data);
            // Nothing to do
        }
    });
});
