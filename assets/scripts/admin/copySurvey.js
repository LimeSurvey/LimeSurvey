/**
 * Ensures Select2 is initialized on the survey picker in the copy survey modal.
 * Called once, binds to modal show event for lazy init.
 */
$(document).ready(function () {
    $('#copySurvey_modal').on('show.bs.modal', function () {
        var $select = $('#surveyIdToCopy');
        if (!$select.data('select2')) {
            $select.select2({
                dropdownParent: $('#copySurvey_modal'),
                theme: 'bootstrap-5',
                ajax: {
                    url: LS.createUrl('surveyAdministration/getAjaxSurveyList'),
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { term: params.term || '' };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    }
                },
                minimumInputLength: 0,
                placeholder: '',
                allowClear: false
            });

            // When source survey changes, update the title field
            $select.on('select2:select', function (e) {
                var data = e.params.data;
                var rawTitle = data.title || '';
                var format = $select.data('copy-format') || 'Copy of %s';
                $('#copysurveytitle').val(format.replace('%s', rawTitle));
            });
        }
    });

    // Handle focus after modal is fully shown
    $('#copySurvey_modal').on('shown.bs.modal', function () {
        var $select = $('#surveyIdToCopy');
        // If source survey is empty, focus on and open the survey picker; otherwise focus on title field
        if (!$select.val()) {
            $select.select2('open');
        } else {
            document.getElementById('copysurveytitle').focus();
        }
    });

    // Reset modal state when it closes to ensure clean state on next open
    $('#copySurvey_modal').on('hidden.bs.modal', function () {
        var $modal = $('#copySurvey_modal');
        var $form = $modal.find('form').first();
        var $select = $('#surveyIdToCopy');

        // Reset the entire form to clear all inputs and checkboxes
        if ($form.length) {
            $form[0].reset();
        }

        // Clear Select2 value using proper Select2 method
        $select.val(null).trigger('change');

        // Remove any custom data attributes
        $select.removeData('copy-format');

        // Collapse the advanced options section
        $('#copySurveyAdvanced').removeClass('show');
    });
});

/**
 * Pre-selects a survey and title in the copy survey modal, then opens it.
 *
 * @param surveyId
 * @param defaultTitle pre-formatted default title for the copy
 * @param surveyText  display text for the pre-selected survey (e.g. "123 - My Survey")
 */
function copySurveyOptions(surveyId, defaultTitle, surveyText) {
        var $select = $('#surveyIdToCopy');

        // Clear any previous selection
        $select.empty();

        // Pre-select the current survey
        if (surveyId) {
            var option = new Option(surveyText || surveyId, surveyId, true, true);
            $select.append(option).trigger('change');
        }

        $('#copysurveytitle').val(defaultTitle || '');
        // Focus is now handled by the shown.bs.modal event handler based on survey selection state
}
