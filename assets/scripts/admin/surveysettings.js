/**
 * Validate settings form depending on form name
 */
 function validateSettingsForm($form) {
    switch ($form.attr('id')) {
        case 'publication':
            return validateEndDateHigherThanStart(
                $('#startdate_datetimepicker').data('DateTimePicker'),
                $('#expires_datetimepicker').data('DateTimePicker'),
                expirationLowerThanStartError
            );
        default:
            return true;
    }
}

/**
 * Validates that an end date is not lower than a start date
 */
function validateEndDateHigherThanStart(startDatePicker, endDatePicker, errorMessage) {
    if (!startDatePicker || !startDatePicker.date()) {
        return true;
    }
    if (!endDatePicker || !endDatePicker.date()) {
        return true;
    }
    const difference = endDatePicker.date().diff(startDatePicker.date());
    if (difference >= 0) {
        return true;
    }
    LS.LsGlobalNotifier.create(errorMessage, 'well-lg bg-danger');
    return false;
}