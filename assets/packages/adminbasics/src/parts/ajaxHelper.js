/**
 * Collection of ajax helper
 */
import * as globals from './globalMethods';
import ajaxAlerts from './ajaxAlerts';

const onSuccess = (response) => {
    
    // Check type of response and take action accordingly
    if (response == '') {
        console.error('No response from server');
        ajaxAlerts('No response from server', 'danger', {showCloseButton: true});
        return false;
    }

    if (!response.loggedIn) {
        // Hide any modals that might be open
        $('.modal').modal('hide');
        $('#ajax-helper-modal .modal-content').html(response.html);
        $('#ajax-helper-modal').modal('show');
        return false;
    }

    // No permission
    if (!response.hasPermission) {
        ajaxAlerts(response.noPermissionText, 'danger', {showCloseButton: true});
        return false;
    }

    // Error popup
    if (response.error) {
        ajaxAlerts(response.error.message, 'danger', {showCloseButton: true});
        return false;
    }

    // Put HTML into element.
    if (response.outputType == 'jsonoutputhtml') {
        $('#' + response.target).html(response.html);
        globals.globalWindowMethods.doToolTip();
        return;
    }

    // Success popup
    if (response.success) {
        ajaxAlerts(response.success, 'success', {showCloseButton: true});
    }

    // Modal popup
    if (response.html) {
        $('#ajax-helper-modal .modal-content').html(response.html);
        $('#ajax-helper-modal').modal('show');
    }

    return true;
};

/**
* Like $.ajax, but with checks for errors,
* permission etc. Should be used together
* with the PHP AjaxHelper.
* @todo Handle error from server (500)?
* @param {object} options - Exactly the same as $.ajax options
* @return {object} ajax promise
*/
const ajax = (options) => {

   var oldSuccess = options.success;
   var oldError = options.error;

   options.success = (response, textStatus, jqXHR) => {

       $('#ls-loading').hide();

       // User-supplied success is always run EXCEPT when login fails
       var runOldSuccess = onSuccess(response);

       if (oldSuccess && runOldSuccess) {
           oldSuccess(response, textStatus, jqXHR);
       }
   }

   options.error = (jqXHR, textStatus, errorThrown) => {
       $('#ls-loading').hide();

       console.error('AJAX CALL FAILED -> ', {
            errorThrown: errorThrown,
            textStatus: textStatus,
            jqXHR: jqXHR,
       });

       if (oldError) {
           oldError(jqXHR, textStatus, errorThrown);
       }
   }

   $('#ls-loading').show();
   return $.ajax(options);
}

export {ajax, onSuccess};
