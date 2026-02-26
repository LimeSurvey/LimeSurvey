/**
 * searches the survey object for a specific keyPath until it reaches the specified depth or returns undefined if not found
 * for example survey: { defaultLanguage: {policyMessage:"..."} }
 */
const reduceValue = (survey, keyPathArrayWithLimit) => {
  return keyPathArrayWithLimit.reduce(
    (accumulator, currentValue) =>
      accumulator && accumulator[currentValue] !== 'undefined'
        ? accumulator[currentValue]
        : undefined,
    survey
  )
}

/**
 * takes a keyPath string and returns the corresponding value from the survey array
 * @param survey {object}
 * @param string {object}
 * @param language {object}
 * @returns {string|*}
 */
export const getSettingValueFromSurvey = (survey, string, language) => {
  if (!language) {
    language = survey.language
  }

  const keyPathArray = string?.keyPath.split('.')
  const isLanguageSetting = keyPathArray[0] === 'languageSettings'

  if (isLanguageSetting) {
    keyPathArray[0] = language
  }

  return keyPathArray.length > 1
    ? (reduceValue(survey, keyPathArray) ?? '')
    : survey?.[string?.keyPath]
}
