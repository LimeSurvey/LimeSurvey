/**
 * Notifcation system for admin
 *
 * @since 2017-08-02
 * @author Olle Haerstedt
 */

// Namespace
var LS = LS || {
    onDocumentReady: {}
};

$(document).on('ready  pjax:scriptcomplete', function() {

    /**
     * Log stuff
     */
    function log(a, b) {
        return; // Remove to enable logging
        if (b == undefined) {
            console.ls.log(a);
        }
        else {
            console.ls.log(a, b);
        }
    }

    /**
     * Load widget HTML and inject it
     * @param {string} URL to call
     * @return
     */
    function updateNotificationWidget(updateUrl) {
        log('updateNotificationWidget');
        // Update notification widget
        return $.ajax({
            url: updateUrl,
            method: 'GET',
            success: function (response) {
                $('#notification-li').replaceWith(response);

                // Re-bind onclick
                initNotification();

                // Adapt style to window size
                styleNotificationMenu();
            }
        });
    }
    /**
     * Called from outside (update notifications when click
     * @param {string} url
     * @param {boolean} openAfter If notification widget should be opened after load; default to true
     * @return
     */
    LS.updateNotificationWidget = function(url, openAfter) {
        // Make sure menu is open after load
        updateNotificationWidget(url).then(function() {
            if (openAfter !== false) {
                $('#notification-li').addClass('open');
            }
        });

        // Only update once
        $('#notification-li').unbind('click');
    }

    /**
     * Tell system that notification is read
     * @param {object} that The notification link
     * @return
     */
    function notificationIsRead(that) {
        log('notificationIsRead');
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
        log('showNotificationModal');
        $.ajax({
            url: url,
            method: 'GET',
        }).done(function(response) {

            var not = response.result;

            $('#admin-notification-modal .modal-title').html(not.title);
            $('#admin-notification-modal .modal-body-text').html(not.message);
            $('#admin-notification-modal .modal-content').addClass('panel-' + not.display_class);
            $('#admin-notification-modal .notification-date').html(not.created.substr(0, 16));
            $('#admin-notification-modal').modal();
            
            // TODO: Will this work in message includes a link that is clicked?
            $('#admin-notification-modal').unbind('hidden.bs.modal');
            $('#admin-notification-modal').on('hidden.bs.modal', function(e) {
                notificationIsRead(that);
                $('#admin-notification-modal .modal-content').removeClass('panel-' + not.display_class);
            });
        });
    }

    /**
     * Bind onclick and stuff
     * @return
     */
    function initNotification() {
        log('initNotification');
        $('.admin-notification-link').each(function(nr, that) {
            
            log('nr', nr);

            var url = $(that).data('url');
            var importance = $(that).data('importance');
            var status = $(that).data('status');

            // Important notifications are shown as pop-up on load
            if (importance == 3 && status == 'new') {
                showNotificationModal(that, url);
                log('stoploop');
                return false;  // Stop loop
            }

            // Bind click to notification in drop-down
            $(that).unbind('click');
            $(that).on('click', function() {
                showNotificationModal(that, url);
            });

        });
    }

    /**
     * Apply styling
     * @return
     */
    function styleNotificationMenu()
    {
        log('styleNotificationMenu');
        var height = window.innerHeight - 70;
        $('#notification-outer-ul').css('height', height + 'px');
        $('#notification-inner-ul').css('height', (height - 60) + 'px');
        $('#notification-inner-li').css('height', (height - 60) + 'px');
    }
    LS.styleNotificationMenu = styleNotificationMenu;

    /**
     * Called when user clicks "Delete all notifications"
     * @param {string} url
     * @return
     */
    function deleteAllNotifications(url, updateUrl) {
        return $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                log('response', response);
            }
        }).then(function() {
            updateNotificationWidget(updateUrl);
        });
    }
    LS.deleteAllNotifications = deleteAllNotifications;

    initNotification();

});
