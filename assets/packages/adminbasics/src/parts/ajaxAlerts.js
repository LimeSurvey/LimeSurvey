/**
 * This class is responsible for creating alerts after ajax requests.
 * It should use bootstrap 5 elements
 * For example: a user is added via a controller action. After the modal is
 * closed (by clicking button "Add") a success alert should be shown.
 *
 * @param message string  | The text to be displayed
 * @param alertType string | bs5 alert types ['success','primary','secondary','danger','warning','info','light','dark',]
 * @param customOptions | possible options are:
 *                         useHtml (boolean) -> use the @message as html
 *                         timeout (int) -> the timeout in milliseconds until the notifier will fade/slide out
 *                         styles (object) -> An object of css-attributes that will be put onto the inner container
 *                         classes (string) -> The classes that will be put onto the inner container
 */

window.LS = window.LS || {};

class AjaxAlerts {

    createAlert(message, alertType, customOptions)
    {
        customOptions = customOptions || {};
        const options = {
            useHtml : customOptions.useHtml || true,
            timeout : customOptions.timeout,
            styles: customOptions.styles || {},
            classes: customOptions.classes || ""
        };

        //bs5 alert types (e.g. alter-success)
        let alertTypesAndIcons = {
            'success': 'ri-checkbox-circle-fill',
            'primary': 'ri-notification-2-line',
            'secondary': 'ri-notification-2-line',
            'danger': 'ri-error-warning-fill',
            'warning': 'ri-alert-fill',
            'info': 'ri-notification-2-line',
            'light': 'ri-notification-2-line',
            'dark': 'ri-notification-2-line',
        };

        let alertDefault = 'success';
        let currentAlertType = alertTypesAndIcons.hasOwnProperty(alertType) ? alertType : alertDefault;
        let iconDefault = 'ri-notification-2-line';
        let icon = alertTypesAndIcons.hasOwnProperty(alertType) ? alertTypesAndIcons[alertType] : iconDefault;
        let iconElement = '<span class="' + icon + ' me-2"></span>';
        let buttonDismiss = '<button type="button" class="btn-close limebutton" data-bs-dismiss="alert" aria-label="Close"></button>';
        const container = $('<div class="alert alert-' + currentAlertType + ' ' + options.classes + ' alert-dismissible" role="alert"></div>');

        if (options.useHtml) {
            container.html(message);
        } else {
            container.text(message);
        }
        $(iconElement).prependTo(container);
        $(buttonDismiss).appendTo(container);

        container.css(options.styles);
        LS.autoCloseAlert(container, options.timeout)

        container.appendTo($('#notif-container'));
    }
}

window.LS.LsGlobalNotifier = window.LS.LsGlobalNotifier || new AjaxAlerts();

export default function (message, alertType) {
    window.LS.LsGlobalNotifier.createAlert(message, alertType);
};

