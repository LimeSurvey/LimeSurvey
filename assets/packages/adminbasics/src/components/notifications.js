/**
 * Notifcation system for admin
 *
 * @since 2017-08-02
 * @author Olle Haerstedt, Markus Flür
 */

import ajaxHelper from '../parts/ajaxHelper';
import LOG from './lslog';

class NotifcationSystem {
    
    /**
     * Load widget HTML and inject it
     * @param {string} URL to call
     * @return
     */
     __updateNotificationWidget(updateUrl) {
        LOG.log('updateNotificationWidget');
        // Update notification widget
        return ajaxHelper({
            url: updateUrl,
            method: 'GET',
            success: (response) => {
                $('#notification-li').replaceWith(response);

                // Re-bind onclick
                this.initNotification();

                // Adapt style to window size
                this.styleNotificationMenu();
            }
        });
    };
    
    /**
     * Tell system that notification is read
     * @param {object} that The notification link
     * @return
     */
    __notificationIsRead(that) {
        LOG.log('notificationIsRead');
        ajaxHelper({
            url: $(that).data('read-url'),
            method: 'GET',
        }).done((response) => {
            // Fetch new HTML for menu widget
            this.__updateNotificationWidget($(that).data('update-url'));
        });

    };
    
    /**
     * Fetch notification as JSON and show modal
     * @param {object} that The notification link
     * @param {url} URL to fetch notification as JSON
     * @return
     */
    __showNotificationModal(that, url) {
        LOG.log('showNotificationModal');
        ajaxHelper({
            url: url,
            method: 'GET',
        }).done((response) => {

            const not = response.result;

            $('#admin-notification-modal .modal-title').html(not.title);
            $('#admin-notification-modal .modal-body-text').html(not.message);
            $('#admin-notification-modal .modal-content').addClass('panel-' + not.display_class);
            $('#admin-notification-modal .notification-date').html(not.created.substr(0, 16));
            $('#admin-notification-modal').modal();
            
            // TODO: Will this work in message includes a link that is clicked?
            $('#admin-notification-modal').off('hidden.bs.modal');
            $('#admin-notification-modal').on('hidden.bs.modal', (e) => {
                this.__notificationIsRead(that);
                $('#admin-notification-modal .modal-content').removeClass('panel-' + not.display_class);
            });
        });
    };

    /*##########PUBLIC##########*/
    /**
     * Bind onclick and stuff
     * @return
     */
    initNotification() {
        // const self = this;
        LOG.group('initNotification');
        $('.admin-notification-link').each((nr, that) => {
            
            LOG.log('Number of Notification: ', nr);

            const url = $(that).data('url');
            const importance = $(that).data('importance');
            const status = $(that).data('status');

            // Important notifications are shown as pop-up on load
            if (importance == 3 && status == 'new') {
                this.__showNotificationModal(that, url);
                LOG.log('stoploop');
                return false;  // Stop loop
            }

            // Bind click to notification in drop-down
            $(that).off('click');
            $(that).on('click', () => {
                this.__showNotificationModal(that, url);
            });

        });
        LOG.groupEnd('initNotification');
    };

    /**
     * Called from outside (update notifications when click
     * @param {string} url
     * @param {boolean} openAfter If notification widget should be opened after load; default to true
     * @return
     */
    
    updateNotificationWidget(url, openAfter) {
        // Make sure menu is open after load
        this.__updateNotificationWidget(url).then(() =>{
            if (openAfter !== false) {
                $('#notification-li').addClass('open');
            }
        });
        // Only update once
        $('#notification-li').off('click');
    };

    /**
     * Apply styling
     * @return
     */
    styleNotificationMenu() {
        LOG.log('styleNotificationMenu');
        const height = window.innerHeight - 70;
        $('#notification-outer-ul').css('height', height + 'px');
        $('#notification-inner-ul').css('height', (height - 60) + 'px');
        $('#notification-inner-li').css('height', (height - 60) + 'px');
    };

    deleteAllNotifications(url, updateUrl) {
        return ajaxHelper({
            url: url,
            method: 'GET',
            success: (response) => {
               LOG.log('response', response);
            }
        }).then(() => {
            this.updateNotificationWidget(updateUrl);
        });
    };
}

//########################################################################

const notificationHelper = new NotifcationSystem();

export default {
    initNotification : ()=> notificationHelper.initNotification.call(notificationHelper, arguments),
    updateNotificationWidget : ()=> notificationHelper.updateNotificationWidget.call(notificationHelper, arguments),
    styleNotificationMenu : ()=> notificationHelper.styleNotificationMenu.call(notificationHelper, arguments),
    deleteAllNotifications : ()=> notificationHelper.deleteAllNotifications.call(notificationHelper, arguments),
};
