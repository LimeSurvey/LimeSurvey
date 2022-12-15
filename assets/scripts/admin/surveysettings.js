/**
 * Validate settings form depending on form name
 */
 function validateSettingsForm($form) {
    switch ($form.attr('id')) {
        case 'publication':
            return LS.validateEndDateHigherThanStart(
                $('#startdate_datetimepicker').data('DateTimePicker'),
                $('#expires_datetimepicker').data('DateTimePicker'),
                () => { LS.LsGlobalNotifier.create(expirationLowerThanStartError, 'alert alert-danger'); }
            );
        default:
            return true;
    }
}
