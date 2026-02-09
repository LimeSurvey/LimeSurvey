$(document).ready(function () {
    let savedViaSwitch = false;

    /**
     * Save the user decision if new editor is turned on or off
     */
    $(document).on("change", "#editor-switch-btn", function () {
        let newValue = $(this).find('.btn-check:checked').val();

        //save feature optin status to db
        let url = $('#saveUrl').val();
        let data = {
            optin: newValue,
        };
        savedViaSwitch = true;
        $.post(
            url,
            data
        ).done(
            function () {
                let successMessage = $('#successMsgFeatureOptout').val();
                if (newValue === "1") {
                    successMessage = $('#successMsgFeatureOptin').val();
                }
                $('#activate_editor').modal('hide');
                LS.ajaxAlerts(successMessage, 'alert-success', {showCloseButton: true});
            }
            ).fail(function () {
                $('#activate_editor').modal('hide');
                let errorMessage = $('#errorOnSave').val();
                LS.ajaxAlerts(errorMessage, 'alert-danger', {showCloseButton: true});
        });
    });
    /**
     * Handle modal close (via close button or clicking outside)
     *     We save the current selected value to make sure we have an entry in the db,
     *     because the modal opens automatically once for users without a saved entry
     */
    $(document).on('hide.bs.modal', '#activate_editor', function () {
        if (!savedViaSwitch) {
            let currentValue = $('#editor-switch-btn').find('.btn-check:checked').val();
            let url = $('#saveUrl').val();
           let data = {
                optin: currentValue,
            };
            $.post(
                url,
            data
            ).fail(function () {
                $('#activate_editor').modal('hide');
                let errorMessage = $('#errorOnSave').val();
                LS.ajaxAlerts(errorMessage, 'alert-danger', {showCloseButton: true});
            });
        }
        // Reset flag for next time
        savedViaSwitch = false;
    });
});
