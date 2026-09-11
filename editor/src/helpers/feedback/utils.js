export const openFeedbackSurveyInNewTab = (surveyId, urlParams) => {
  window.open(
    `https://survey.limesurvey.org/${surveyId}?${urlParams.toString()}`,
    '_blank'
  )
}
