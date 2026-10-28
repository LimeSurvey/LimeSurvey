/**
 * API token form: add and remove survey specific permissions
 */

function setApiTokenSurveyStatus(text) {
    $('#apitoken-surveys-status').text(text);
}

function setApiTokenSurveyOptionDisabled(surveyId, disabled) {
    $('#apitoken-add-survey option[value="' + surveyId + '"]').prop('disabled', disabled);
}

$(document).on('click', '#apitoken-add-survey-button', function () {
    var $select = $('#apitoken-add-survey');
    var surveyId = $select.val();
    if (!surveyId || $('.apitoken-survey[data-surveyid="' + surveyId + '"]').length) {
        $select.trigger('focus');
        return;
    }
    var url = new URL($(this).data('url'), window.location.href);
    url.searchParams.set('surveyid', surveyId);
    $.get(url.toString())
        .done(function (html) {
            var $survey = $(html);
            $('#apitoken-surveys').append($survey);
            setApiTokenSurveyOptionDisabled(surveyId, true);
            $select.val('');
            setApiTokenSurveyStatus(LS.apiTokenTexts.surveyAdded);
            $survey.find('input[type="checkbox"]').first().trigger('focus');
        })
        .fail(function () {
            setApiTokenSurveyStatus(LS.apiTokenTexts.surveyError);
        });
});

$(document).on('click', '.apitoken-survey-remove', function () {
    var $survey = $(this).closest('.apitoken-survey');
    setApiTokenSurveyOptionDisabled($survey.data('surveyid'), false);
    $survey.remove();
    setApiTokenSurveyStatus(LS.apiTokenTexts.surveyRemoved);
    $('#apitoken-add-survey').trigger('focus');
});
