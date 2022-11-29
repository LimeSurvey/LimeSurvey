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
            timeout : customOptions.timeout || 3500,
            styles: customOptions.styles || {},
            classes: customOptions.classes || ""
        };

        //bs5 alert types (e.g. alter-success)
        let alertTypes = [
            'success',
            'primary',
            'secondary',
            'danger',
            'warning',
            'info',
            'light',
            'dark',
        ];

        let alertDefault = 'success';
        let currentAlertType = alertTypes.includes(alertType) ? alertType : alertDefault;
        let openDivTag = '<div class="alert alert-' + currentAlertType + ' ' + options.classes + ' alert-dismissible" role="alert">';
        let buttonDismiss = '<button type="button" class="btn-close limebutton" data-bs-dismiss="alert" aria-label="Close"></button>';

        const container = $(openDivTag + buttonDismiss + '</div>');

        if (options.useHtml) {
            container.html(message);
        } else {
            container.text(message);
        }

        container.css(options.styles);
        let timeoutRef = setTimeout(() => { container.alert('close') }, options.timeout);

        container.on('closed.bs.alert', () => {
            clearTimeout(timeoutRef);
        });

        container.appendTo($('#notif-container'));
    }
}

window.LS.LsGlobalNotifier = window.LS.LsGlobalNotifier || new AjaxAlerts();

export default function (message, alertType) {
    window.LS.LsGlobalNotifier.createAlert(message, alertType);
};

