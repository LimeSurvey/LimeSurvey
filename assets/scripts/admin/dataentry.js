// Clear the validity status when inputs are changed. We'll check the validity again when the form is submitted.
$(document).on('change', 'input:not([type="hidden"]):not([readonly]), select, textarea', function () {
    const answerWrapper = $(this).closest('.answer-wrapper');
    if (answerWrapper.length == 0) {
        return;
    }
    const fieldName = answerWrapper.attr('data-field');
    const unseenCheckbox = $('#responsedetail input[type="checkbox"][name="unseen:' + fieldName + '"]').get(0);
    unseenCheckbox.setCustomValidity('');
    unseenCheckbox.reportValidity();
});
$(document).on('change', '#responsedetail input[type="checkbox"][name^="unseen:"]', function () {
    $(this).get(0).setCustomValidity('');
    $(this).get(0).reportValidity();
});

$(document).on('submit', '#editresponse', function (event) {
    let hasInconsistentUnseenStatus = false;
    $('#responsedetail > tbody > tr').each(function (rowIndex, tr) {
        const unseenCheckboxes = $(tr).find('input[type="checkbox"][name^="unseen:"]:checked');
        unseenCheckboxes.each(function (checkboxIndex, unseenCheckbox) {
            // The checkbox name is in the format "unseen:FIELDNAME", we only want the FIELDNAME part
            const fieldName = $(unseenCheckbox).attr('name').split(':')[1];
            const answerWrapper = $('.answer-wrapper[data-field="' + fieldName + '"]');
            const inputs = answerWrapper.find('input:not([type="hidden"]):not([readonly]), select, textarea');

            let isNotEmpty = false;
            inputs.each(function (inputIndex, input) {
                if ($(input).attr('type') === 'checkbox') {
                    if ($(input).prop('checked')) {
                        isNotEmpty = true;
                    }
                } else if ($(input).attr('type') === 'radio') {
                    if ($(input).prop('checked') && $(input).val() != '') {
                        isNotEmpty = true;
                    }
                } else if ($(input).val() != '') {
                    isNotEmpty = true;
                }
                // Break out of the loop if we've found a non-empty value
                if (isNotEmpty) {
                    return false;
                }
            });

            // Mark the "Unseen" checkbox as invalid if the answer is not empty
            if (isNotEmpty) {
                // Note: invalidUnseenCheckboxMessage is defined in PHP
                unseenCheckbox.setCustomValidity(invalidUnseenCheckboxMessage);
                unseenCheckbox.reportValidity();
                hasInconsistentUnseenStatus = true;
            }
        });
    });

    if (hasInconsistentUnseenStatus) {
        event.preventDefault();
        return false;
    }
});