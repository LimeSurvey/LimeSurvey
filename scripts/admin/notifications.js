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
     * Log stuff
     */
    function log(a, b) {
        return; // Remove to enable logging
        if (b == undefined) {
            console.log(a);
        }
        else {
            console.log(a, b);
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

            var response = JSON.parse(response);
            var not = response.result;

            $('#admin-notification-modal .modal-title').html(not.title);
            $('#admin-notification-modal .modal-body-text').html(not.message);
            $('#admin-notification-modal .modal-content').addClass('panel-' + not.modal_class);
            $('#admin-notification-modal').modal();
            
            // TODO: Will this work in message includes a link that is clicked?
            $('#admin-notification-modal').unbind('hidden.bs.modal');
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
        log('initNotification');
        $('.admin-notification-link').each(function(nr, that) {
            
            log('nr', nr);

            var url = $(that).data('url');
            var type = $(that).data('type');
            var status = $(that).data('status');

            // Important notifications are shown as pop-up on load
            if (type == 'important' && status == 'new') {
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
     */
    function styleNotificationMenu(that)
    {
        var height = window.innerHeight - 100;
        //$(that).find('#notification-outer-ul').css('height', '400px');
        //$(that).find('#notification-outer-ul').css('width', '350px');
        //$(that).find('#notification-inner-ul').css('height', '340px');
        //$(that).find('#notification-inner-ul').css('width', '340px');
        //$(that).find('.dropdown-menu').css('overflow-y', 'scroll');

        //$('#notification-clear-all').css('top', (height + 50) + 'px');
        //$('#notification-clear-all a').css('padding', '3px 20px');
        //$('#notification-clear-all').css('width', '333px');
        //$('#notification-clear-all').css('height', '47');
    }
    LS.styleNotificationMenu = styleNotificationMenu;

    /**
     * Called when user clicks "Delete all notifications"
     * @param {string} url
     * @return
     */
    function deleteAllNotifications(url, updateUrl) {
        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                console.log('response', response);
            }
        }).then(function() {
            updateNotificationWidget(updateUrl);
        });;
    }
    LS.deleteAllNotifications = deleteAllNotifications;

    initNotification();

});
