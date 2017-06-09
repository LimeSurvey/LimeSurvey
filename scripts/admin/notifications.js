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
                // Adapt style to window size
                styleNotificationMenu();
            },
            error: showError
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
            success : function(response) {
                // Fetch new HTML for menu widget
                updateNotificationWidget($(that).data('update-url'));
            },
            error: showError
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
            dataType: 'json',
            success : function(response) {
                var not = response.result;
                $('#admin-notification-modal .modal-title').html(not.title);
                $('#admin-notification-modal .modal-body-text').html(not.message);
                $('#admin-notification-modal .modal-content').removeClass('panel-danger').addClass('panel-' + not.display_class);
                $('#admin-notification-modal .notification-date').html(not.created.substr(0, 16));
                $('#admin-notification-modal').modal();
                
                // TODO: Will this work in message includes a link that is clicked?
                $('#admin-notification-modal').unbind('hidden.bs.modal');
                $('#admin-notification-modal').on('hidden.bs.modal', function(e) {
                    notificationIsRead(that);
                    $('#admin-notification-modal .modal-content').removeClass('panel-' + not.display_class);
                });
            },
            error: showError
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
            },
            error: showError
        }).then(function() {
            updateNotificationWidget(updateUrl);
        });
    }
    LS.deleteAllNotifications = deleteAllNotifications;

    initNotification();

    function showError(response) {
        var status= response.status || LS.lang.errorUnknow;
        var responseText= response.responseText || LS.lang.unknowText;
        $('#admin-notification-modal .modal-title').html(LS.lang.errorTitle.replace("%s",status));
        $('#admin-notification-modal .modal-body-text').html(responseText);
        $('#admin-notification-modal .modal-content').addClass('panel-danger');
        //$('#admin-notification-modal .notification-date').html(not.created.substr(0, 16));
        $('#admin-notification-modal').modal();
        $('#admin-notification-modal').unbind('hidden.bs.modal');
        $('#admin-notification-modal').on('hidden.bs.modal', function(e) {
            $('#admin-notification-modal .modal-content').removeClass('panel-danger');
        });
    }
});
