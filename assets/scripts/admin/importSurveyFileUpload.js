$(document).on('ready pjax:scriptcomplete', function () {
    const form = $('#importsurvey');
    if (form.length === 0) {
        return;
    }

    //full area where files can be dragged and dropped
    const dropZone = document.getElementById('drop_zone');

    //the normal way to upload files for importing a survey
    const inputFieldFile = document.getElementById('fileUpload');

    //the text field , where the user can see the file names he wants to import
    const textField = document.getElementById('file-upload-text');
    const allowedExtensions = ['lss', 'lsa', 'txt', 'tsv'];
    const maximumFileSize = 40 * 1024 * 1024;

    /**
     * Append file name to text field.
     */
    function changeTextAfterFileIsChanged(filename) {
        textField.textContent = filename + ('\n');
    }

    function showDropZonePrompt() {
        $(textField)
            .empty()
            .append($('<span>', {class: 'ri-upload-line', 'aria-hidden': 'true'}))
            .append($('<span>', {text: form.data('drop-zone-text')}));
    }

    function resetSelectedFile() {
        inputFieldFile.value = '';
        dropZone.classList.add('upload-label--invalid');
        showDropZonePrompt();
        $('#import-submit').prop('disabled', true);
    }

    function validateFile(file) {
        const extension = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
        if (!allowedExtensions.includes(extension)) {
            return form.data('error-file-type');
        }

        if (file.size > maximumFileSize) {
            return form.data('error-file-size');
        }

        return '';
    }

    function acceptFiles(files, dropped) {
        if (files.length !== 1) {
            resetSelectedFile();
            showImportNotification(form.data('error-file-count'), 'danger');
            return false;
        }

        const file = files[0];
        const validationError = validateFile(file);
        if (validationError) {
            resetSelectedFile();
            showImportNotification(validationError, 'danger');
            return false;
        }

        if (dropped) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            inputFieldFile.files = dataTransfer.files;
        }

        dropZone.classList.remove('upload-label--invalid');
        changeTextAfterFileIsChanged(file.name);
        $('#import-submit').prop('disabled', false);
        return true;
    }

    $(inputFieldFile).off('change.importSurvey').on('change.importSurvey', function (event) {
        acceptFiles(event.target.files, false);
    });

    function dropHandler(event) {
        // Prevent default behavior (Prevent file from being opened)
        event.preventDefault();
        acceptFiles(event.dataTransfer.files, true);
    }

    //to prevent to just open file content in new tab
    $(dropZone).off('dragover.importSurvey').on('dragover.importSurvey', (event) => {
        event.preventDefault();
    });

    $(dropZone).off('drop.importSurvey').on('drop.importSurvey', (event) => {
        dropHandler(event.originalEvent);
    });

    function showForm() {
        form[0].reset();
        form.data('import-completed', false);
        dropZone.classList.remove('upload-label--invalid');
        showDropZonePrompt();
        form.closest('.modal-content').find('.modal-title').text(form.data('form-title'));
        $('#import-survey-form-content, #import-survey-form-footer').removeClass('d-none');
        $('#import-survey-summary, #import-survey-summary-footer').addClass('d-none');
        $('#import-survey-summary-rows, #import-survey-summary-warnings ul').empty();
        $('#import-survey-summary-warnings').addClass('d-none');
        $('#import-submit').prop('disabled', true);
    }

    function showSummary(result) {
        const labels = form.data('summary-labels') || {};
        const rows = $('#import-survey-summary-rows').empty();

        Object.keys(labels).forEach(function (key) {
            if (result.summary[key] === undefined || result.summary[key] === null) {
                return;
            }
            $('<div>', {class: 'import-survey-summary__row', role: 'row'})
                .append($('<span>', {
                    class: 'import-survey-summary__label',
                    role: 'rowheader',
                    text: labels[key]
                }))
                .append($('<span>', {
                    class: 'import-survey-summary__value',
                    role: 'cell',
                    text: result.summary[key]
                }))
                .appendTo(rows);
        });

        const warnings = result.summary.importwarnings || [];
        if (warnings.length > 0) {
            const warningList = $('#import-survey-summary-warnings ul').empty();
            warnings.forEach(function (warning) {
                $('<li>', {text: $('<textarea>').html(warning).text()}).appendTo(warningList);
            });
            $('#import-survey-summary-warnings').removeClass('d-none');
        }

        $('#import-survey-go-to-survey').attr('href', result.surveyUrl);
        form.closest('.modal-content').find('.modal-title').text(form.data('summary-title'));
        $('#import-survey-form-content, #import-survey-form-footer').addClass('d-none');
        $('#import-survey-summary, #import-survey-summary-footer').removeClass('d-none');
    }

    function showImportNotification(message, type) {
        const modifier = type === 'success' ? 'success' : 'error';
        window.LS.ajaxAlerts(message, type, {
            htmlOptions: JSON.stringify({
                class: 'import-survey-toast import-survey-toast--' + modifier
            }),
            showCloseButton: true
        });
    }

    form.off('submit.importSurvey').on('submit.importSurvey', function (event) {
        event.preventDefault();

        //Check input fields are filled
        //check file ending
        if (inputFieldFile.files.length === 0) {
            showImportNotification(form.data('error-file-required'), 'danger');
            return false;
        }

        const validationError = validateFile(inputFieldFile.files[0]);
        if (validationError) {
            resetSelectedFile();
            showImportNotification(validationError, 'danger');
            return false;
        }

        const submitButton = $('#import-submit').prop('disabled', true);
        $('#ls-loading').show();

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(form[0]),
            contentType: false,
            processData: false,
            dataType: 'json'
        }).done(function (result) {
            if (!result.success) {
                showImportNotification(result.error || form.data('error-message'), 'danger');
                return;
            }

            showSummary(result);
            form.data('import-completed', true);
            showImportNotification(form.data('success-message'), 'success');
        }).fail(function (request) {
            const message = request.responseJSON && request.responseJSON.error
                ? request.responseJSON.error
                : form.data('error-message');
            showImportNotification(message, 'danger');
        }).always(function () {
            $('#ls-loading').hide();
            submitButton.prop('disabled', inputFieldFile.files.length === 0);
        });

        return false;
    });

    $('#importSurvey_modal')
        .off('hidden.bs.modal.importSurvey')
        .on('hidden.bs.modal.importSurvey', function () {
            if (form.data('import-completed')) {
                window.location.reload();
                return;
            }
            showForm();
        });
});
