export const getNoAnswerLabel = (useForSurveyPreview = false) => {
  if (useForSurveyPreview) {
    return st('No answer')
  } else {
    return t('No answer')
  }
}
