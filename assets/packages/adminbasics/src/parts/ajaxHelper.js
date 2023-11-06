/**
 * Collection of ajax helper
 */
import * as globals from './globalMethods';
import notifyFader from './notifyFader';

const onSuccess = (response) => {
    
    // Check type of response and take action accordingly
    if (response == '') {
        console.error('No response from server');
        notifyFader.create('No response from server', 'alert-danger');
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
        notifyFader(response.noPermissionText, 'well-lg bg-danger text-center');
        return false;
    }

    // Error popup
    if (response.error) {
        notifyFader(response.error.message, 'well-lg bg-danger text-center');
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
        notifyFader(response.success, 'well-lg bg-primary text-center');
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
