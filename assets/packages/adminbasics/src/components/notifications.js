/**
 * Notifcation system for admin
 *
 * @since 2017-08-02
 * @author Olle Haerstedt, Markus Flür
 */

import * as AjaxHelper from '../parts/ajaxHelper';
import LOG from './lslog';

const NotifcationSystem  = function (){
    const
    /**
     * Load widget HTML and inject it
     * @param {string} URL to call
     * @return
     */
     __updateNotificationWidget = (updateUrl) => {
        LOG.log('updateNotificationWidget');
        // Update notification widget
        return $.ajax({
            url: updateUrl,
            method: 'GET',
            success: (response) => {
                $('#notification-li').replaceWith(response);

                // Re-bind onclick
                initNotification();

                // Adapt style to window size
                styleNotificationMenu();
            }
        });
    },
    
    /**
     * Tell system that notification is read
     * @param {object} that The notification link
     * @return
     */
    __notificationIsRead = (that) => {
        LOG.log('notificationIsRead');
        return $.ajax({
            url: $(that).data('read-url'),
            method: 'GET',
        }).then(() => {
            // Fetch new HTML for menu widget; return so callers can chain on completion.
            return __updateNotificationWidget($(that).data('update-url'));
        });

    },
    
    /**
     * Fetch notification as JSON and show modal
     * @param {object} that The notification link
     * @param {url} URL to fetch notification as JSON
     * @return
     */
    __showNotificationModal = (that, url) => {
        LOG.log('showNotificationModal');
        $.ajax({
            url: url,
            method: 'GET',
        }).done((response) => {

            const not = response.result;

            $('#admin-notification-modal .modal-title').html(not.title);
            $('#admin-notification-modal .modal-body-text').html(not.message);
            $('#admin-notification-modal .modal-content').addClass('card-' + not.display_class);
            $('#admin-notification-modal .notification-date').html(not.created.substr(0, 16));

            const modal = new bootstrap.Modal(document.getElementById('admin-notification-modal'));
            modal.show();

            // Move screen reader / keyboard focus to the modal title once the modal is visible
            $('#admin-notification-modal').one('shown.bs.modal', () => {
                const title = document.getElementById('admin-notification-modal-title');
                if (title) {
                    title.focus();
                }
            });

            // TODO: Will this work in message includes a link that is clicked?
            $('#admin-notification-modal').off('hidden.bs.modal');
            $('#admin-notification-modal').on('hidden.bs.modal', (e) => {
                $('#admin-notification-modal .modal-content').removeClass('card-' + not.display_class);
                // Restore focus only after __updateNotificationWidget() has completed and
                // replaced the menu toggle, so the element we focus is already in the DOM.
                __notificationIsRead(that).then(() => {
                    const dropdownToggle = document.getElementById('admin-notifications-menu-button');
                    if (dropdownToggle) {
                        dropdownToggle.focus();
                    }
                });
            });
        });
    },

    /*##########PUBLIC##########*/
    /**
     * Bind onclick and stuff
     * @return
     */
    initNotification = () => {
        // const self = this;
        
        $('.admin-notification-link').each((nr, that) => {
            
            LOG.log('Number of Notification: ', nr);

            const url = $(that).data('url');
            const importance = $(that).data('importance');
            const status = $(that).data('status');

            // Important 2 = nag only once (used e.g. for redirect).
            if (importance == 2 && status == 'new') {
                __showNotificationModal(that, url);
                // __notificationIsRead is called by the hidden.bs.modal handler
                // registered inside __showNotificationModal; no second call needed.
                LOG.log('stoploop');
                return false;  // Stop loop
            }

            // Important notifications are shown as pop-up on load
            if (importance == 3 && status == 'new') {
                __showNotificationModal(that, url);
                LOG.log('stoploop');
                return false;  // Stop loop
            }

            // Bind click to notification in drop-down
            $(that).off('click.showNotification');
            $(that).on('click.showNotification', () => {
                __showNotificationModal(that, url);
            });

        });
        
    },

    /**
     * Called from outside (update notifications when click
     * @param {string} url
     * @param {boolean} openAfter If notification widget should be opened after load; default to true
     * @return
     */
    
    updateNotificationWidget = (url, openAfter) => {
        // Make sure menu is open after load
        __updateNotificationWidget(url).then(() => {
            let dropdownToggleEl = document.querySelector('#notification-li .dropdown-toggle');
            let dropdownList = new bootstrap.Dropdown(dropdownToggleEl);
            dropdownList.show();
        });
        // Only update once
        $('#notification-li').off('click.showNotification');
    },

    /**
     * Apply styling
     * @return
     */
    styleNotificationMenu = () => {
        LOG.log('styleNotificationMenu');
        const height = window.innerHeight - 70;
        $('#notification-outer-ul').css('height', height + 'px');
        $('#notification-inner-ul').css('height', (height - 60) + 'px');
        $('#notification-inner-li').css('height', (height - 60) + 'px');
    },

    deleteAllNotifications = (url, updateUrl) => {
        let data = document.querySelector('#notification-clear-all > a').getAttribute('data-params');
        return $.ajax({
            url: url,
            data: data,
            method: 'POST',
            success: (response) => {
               LOG.log('response', response);
            }
        }).then(() => {
            updateNotificationWidget(updateUrl);
        });
    };

    return {
        initNotification,
        updateNotificationWidget,
        styleNotificationMenu,
        deleteAllNotifications,
    }
}

//########################################################################

const notificationSystem = new NotifcationSystem();

export default notificationSystem;
