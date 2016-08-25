/**
 * This file is included ONLY when a order is
 * in "blocking" state (hold, new, live).
 * This is to update the
 * Cint order status at each page reload using
 * Ajax, so the page load won't be slower.
 *
 * Sets a cookie that expires in 20 min, so will
 * only check each 20 min to save some bandwidth.
 *
 * @since 2016-08-05
 */

var LS = LS || {};
$(document).ready(function() {

    console.log('CintLink checkOrders');

    /**
     * Cookie code copied from http://stackoverflow.com/questions/1458724/how-do-i-set-unset-cookie-with-jquery
     */
	function createCookie(name, value, seconds) {
		var expires;

		if (seconds) {
			var date = new Date();
			date.setTime(date.getTime() + (seconds * 1000));
			expires = "; expires=" + date.toGMTString();
		} else {
			expires = "";
		}
		document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
	}
	function readCookie(name) {
		var nameEQ = encodeURIComponent(name) + "=";
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) === ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
		}
		return null;
	}
	function eraseCookie(name) {
		createCookie(name, "", -1);
	}

    var cookie = readCookie('cintlink_checkorder');

    if (cookie == null) {
        $.ajax({
            url: LS.plugin.cintlink.pluginBaseUrl + '&method=updateAllOrders',
            surveyId: LS.plugin.cintlink.surveyId,
            success: function(data) {
                // Create cookie that expires in 20 min
                createCookie('cintlink_checkorder', '1', 60 * 20);
                console.log('Creating new checkorder cookie');
            }
        });
    }
});
