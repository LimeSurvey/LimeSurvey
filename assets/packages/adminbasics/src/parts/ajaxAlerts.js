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

import createUrl from "./createUrl";

window.LS = window.LS || {};

class AjaxAlerts {

    /**
     * Creates by default a notification alert (e.g. success message) after save,
     * which pops up as banner hovering over the other elements.
     * If you want an inline alert, just pass customOptions['inline'] = 'name/class/id of the container where it should be in'
     *
     * @param message
     * @param alertType
     * @param customOptions
     */
    createAlert(message, alertType, customOptions) {
        customOptions = customOptions || {};
        customOptions.isFilled = customOptions.isFilled || false;
        customOptions.inline = customOptions.inline || false;
        if (customOptions.inline !== false) {
            //inline alert is always filled
            customOptions.isFilled = true;
        } else {
            customOptions.isFilled = customOptions.isFilled || false;
        }
        const options = {
            timeout: customOptions.timeout,
            styles: customOptions.styles || {},
            classes: customOptions.classes || ""
        };

        LS.getAlertHtml(message, alertType, customOptions).then(function (response) {
            const container = $(response);
            container.css(options.styles);
            if(customOptions.inline !== false) {
                //inline alert is always appended to the passed element
                container.appendTo($(customOptions.inline));
            }else{
                //modalish alert is always autoclosed and added to the element #notif-container
                LS.autoCloseAlert(container, options.timeout)
                container.appendTo($('#notif-container'));
            }
        }).catch(function (err) {
            console.log(err);
        });
    }
}

window.LS.LsGlobalNotifier = window.LS.LsGlobalNotifier || new AjaxAlerts();

export default function (message, alertType, customOptions) {
    window.LS.LsGlobalNotifier.createAlert(message, alertType, customOptions);
};

const ajaxAlertMethod = {
    /**
     * Returns the Promise of the html which the AlertWidget returns
     * @param message
     * @param alertType
     * @param customOptions
     * @returns {Promise}
     */
    getAlertHtml: (message, alertType, customOptions) => {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: LS.createUrl('ajaxAlert/getAlertWidget'),
                data: {message: message, alertType: alertType, customOptions: customOptions},
                method: 'POST',
                success: function (response) {
                    resolve(response);
                },
                error: function (response) {
                    console.log(response);
                    reject(response);
                }
            });
        });
    },
}

export {ajaxAlertMethod};


