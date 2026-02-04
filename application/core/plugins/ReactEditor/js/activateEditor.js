

$(document).ready(function () {
    let savedViaSwitch = false;
    //todo we decided to use the switch again as a trigger
    $(document).on("change", "#editor-switch-btn", function () {
        let newValue = $(this).find('.btn-check:checked').val();

        //save feature optin status to db
        let url = $('#saveUrl').val();
        let data = {
            optin: newValue,
        };
        savedViaSwitch = true;
        $.post(url, data, function () {
            let successMessage = $('#successMsgFeatureOptout').val();
            if (newValue === "1") {
                successMessage = $('#successMsgFeatureOptin').val();
            }
            $('#activate_editor').modal('hide');
            LS.ajaxAlerts(successMessage, 'alert-success', {showCloseButton: true});
            // Wait 2 seconds before reloading
           /* setTimeout(function() {
                window.location.reload();
            }, 2000); */
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
            $.post(url, data);
        }
        // Reset flag for next time
        savedViaSwitch = false;
    });
});
