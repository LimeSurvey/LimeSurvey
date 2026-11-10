$(document).on('ready  pjax:scriptcomplete', function(){

    //full area where files can be dragged and dropped
    const dropZone = document.getElementById('drop_zone');

    //the normal way to upload files for importing a survey
    const inputFieldFile = document.getElementById('fileUpload');

    //the text field , where the user can see the file names he wants to import
    const textField = document.getElementById('file-upload-text');

    if (!dropZone || !inputFieldFile || !textField) {
        return;
    }

    /**
     * Append file name to text field.
     */
    function changeTextAfterFileIsChanged(filename){
        textField.textContent = filename + ('\n');
    }

    //the error message of the file field, in an alert region
    const errorField = document.getElementById('fileUpload-error');

    //pending timeout that writes the error message into the alert region
    let errorTimeout = null;

    /**
     * Show or clear the "No file selected" error of the file field.
     * The alert region is emptied first and filled after a short delay, so screen readers
     * announce the message again when the same error is shown twice in a row.
     *
     * @param {string} message Error message to show, or an empty string to clear the error
     * @return {void}
     */
    function setFileError(message) {
        clearTimeout(errorTimeout);
        if (errorField) {
            errorField.textContent = '';
            if (message) {
                errorTimeout = setTimeout(function () {
                    errorField.textContent = message;
                }, 100);
            }
        }
        if (message) {
            inputFieldFile.setAttribute('aria-invalid', 'true');
        } else {
            inputFieldFile.removeAttribute('aria-invalid');
        }
    }

    // Handlers are namespaced and unbound first: This runs again on every pjax navigation,
    // while the modal (part of the admin menu) stays in the page
    $(inputFieldFile).off('change.importSurvey').on('change.importSurvey', function(event) {
        const files = event.target.files; // This is a FileList object
        for (const file of files) {
            changeTextAfterFileIsChanged(file.name);
        }
        setFileError('');
    });

    function dropHandler(event) {
        // Prevent default behavior (Prevent file from being opened)
        event.preventDefault();
        //show file name(s), instead of default text when dropping or adding files to the drop_zone
        const droppedFiles = event.dataTransfer.files;

        // Create a new DataTransfer object to simulate file input
        const dataTransfer = new DataTransfer();
        for (const file of droppedFiles) {
            dataTransfer.items.add(file);
            changeTextAfterFileIsChanged(file.name);
        }
        inputFieldFile.files = dataTransfer.files;
        setFileError('');
    }

    //to prevent to just open file content in new tab
    $(dropZone).off('dragover.importSurvey').on('dragover.importSurvey', function (event) {
        event.preventDefault();
    });

    $(dropZone).off('drop.importSurvey').on('drop.importSurvey', function (event) {
        dropHandler(event.originalEvent);
    });

    $('#importsurvey').off('submit.importSurvey').on('submit.importSurvey', function() {
        //Check input fields are filled
        //check file ending
        if (inputFieldFile.files.length === 0) {
            setFileError($('#importsurvey').data('error-file-required'));
            inputFieldFile.focus();
            return false;
        }

        // submit form here
        return true;
    });

    // Clear the error when the modal is closed, so it doesn't show up again on the next open
    $('#importSurvey_modal').off('hidden.bs.modal.importSurvey').on('hidden.bs.modal.importSurvey', function () {
        setFileError('');
    });

});
