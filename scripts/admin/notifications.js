/**
 * Notifcation system for admin
 *
 * @since 2017-08-02
 * @author Olle Haerstedt
 */

// Namespace
var LS = LS || {};

$(document).ready(function() {

    /**
     * Load widget HTML and inject it
     * @param {string} URL to call
     * @return
     */
    function updateNotificationWidget(updateUrl) {
        // Update notification widget
        return $.ajax({
            url: updateUrl,
            method: 'GET',
            success: function (response) {
                $('#notification-li').replaceWith(response);

                // Re-bind onclick
                initNotification();
            }
        });
    }
    // Called from outside (update notifications when click
    LS.updateNotificationWidget = function(url) {
        // Make sure menu is open after load
        updateNotificationWidget(url).then(function() {
            $('#notification-li').addClass('open');
        });

        // Only update once
        LS.updateNotificationWidget = function() {};
    }

    /**
     * Tell system that notification is read
     * @param {object} that The notification link
     * @return
     */
    function notificationIsRead(that) {
        $.ajax({
            url: $(that).data('read-url'),
            method: 'GET',
        }).done(function(response) {
            // Fetch new HTML for menu widget
            updateNotificationWidget($(that).data('update-url'));
        });

    }

    /**
     * Fetch notification as JSON and show modal
     * @param {object} that The notification link
     * @param {url} URL to fetch notification as JSON
     * @return
     */
    function showNotificationModal(that, url) {
        $.ajax({
            url: url,
            method: 'GET',
        }).done(function(response) {

            var response = JSON.parse(response);
            var not = response.result;

            $('#admin-notification-modal .modal-title').html(not.title);
            $('#admin-notification-modal .modal-body-text').html(not.message);
            $('#admin-notification-modal .modal-content').addClass('panel-' + not.modal_class);
            $('#admin-notification-modal').modal();
            
            // TODO: Will this work in message includes a link that is clicked?
            $('#admin-notification-modal').on('hidden.bs.modal', function(e) {
                notificationIsRead(that);
            });
        });
    }

    /**
     * Bind onclick and stuff
     * @return
     */
    function initNotification() {
        $('.admin-notification-link').each(function(nr, that) {
            
            console.log('nr', nr);

            var url = $(that).data('url');
            var type = $(that).data('type');

            // Important notifications are shown as pop-up on load
            if (type == 'important') {
                showNotificationModal(that, url);
                console.log('stoploop');
                return false;  // Stop loop
            }

            // Bind click to notification in drop-down
            $(that).unbind('click');
            $(that).on('click', function() {
                showNotificationModal(that, url);
            });

        });
    }

    initNotification();

});
