/**
 * Sets the survey id in the modal to copy survey.
 *
 * @param surveyId
 */
function copySurveyOptions(surveyId) {
        $('#surveyIdToCopy').val(surveyId);
}

function copySurvey(surveyId) {
    // Perform the AJAX request to copy the survey
    $.ajax({
        url: LS.createUrl('surveyAdministration/copy'),
        type: 'POST',
        data: { surveyIdToCopy: surveyId }
    });
}

