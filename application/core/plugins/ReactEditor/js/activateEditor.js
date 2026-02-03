

$(document).ready(function () {
    //todo we decided to use the switch again as a trigger
    $(document).on("change", "#editor-switch-btn", function () {
        let newValue = $(this).find('.btn-check:checked').val();

        //save feature optin status to db
        let url = $('#saveUrl').val();
        let data = {
            optin: newValue,
        };
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
});
