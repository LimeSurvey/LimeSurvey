/**
 * This class is responsible for creating alerts after ajax requests.
 * It should use bootstrap 5 elements
 * For example: a user is added via a controller action. After the modal is
 * closed (by clicking button "Add") a success alert should be shown.
 */

window.LS = window.LS || {};

class AjaxAlerts {

    createAlert(message, alertType)
    {
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
        let openDivTag = '<div class="alert alert-' + currentAlertType + ' alert-dismissible" role="alert">';
        let buttonDismiss = '<button type="button" class="btn-close limebutton" data-bs-dismiss="alert" aria-label="Close"></button>';

        $('#notif-container').append(openDivTag + buttonDismiss + message + '</div>');
    }
}

window.LS.LsGlobalNotifier = window.LS.LsGlobalNotifier || new AjaxAlerts();

export default function (message, alertType) {
    window.LS.LsGlobalNotifier.createAlert(message, alertType);
};

