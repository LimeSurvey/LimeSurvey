import { mapValues } from 'lodash'

import questionTypesData from './data/question-types.json'

/**
 * Returns the question types of a default installation (the questionTypes API response of a core install),
 * used when the API can't be reached. The titles are translated by the editor: They are the texts the
 * backend translates, listed for the translation script on the PHP side (react/translations.php and the
 * question theme translations).
 * @param {string} language Language of the titles
 * @returns {{categories: Object[], questionTypes: Object<string, Object>}}
 */
export const getDefaultQuestionTypes = (language) => {
  const translate = (text) => t(text, { lng: language }) || text

  return {
    categories: questionTypesData.categories.map((category) => ({
      ...category,
      title: translate(category.title),
    })),
    questionTypes: mapValues(
      questionTypesData.questionTypes,
      (questionType) => ({
        ...questionType,
        title: translate(questionType.title),
      })
    ),
  }
}
