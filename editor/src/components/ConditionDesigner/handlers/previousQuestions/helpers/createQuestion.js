import { getQuestionTypeInfo } from 'components/QuestionTypes/index.js'

import { getQuestionText } from '../helpers'

/**
 * Returns the text of a question, or its code when it has no text yet, so a
 * new question isn't listed without a name.
 *
 * @param {object} question The question.
 * @param {string} language The language of the text.
 * @returns {string} The label.
 */
const getTextOrCode = (question, language) =>
  getQuestionText(question, language) || question.title || ''

/**
 * Adds a condition question for a question or one of its subquestions.
 *
 * @param {object[]} cQuestions The condition questions list, extended in place.
 * @param {object} question The question.
 * @param {object|null} subquestion The subquestion, or null for the question itself.
 * @param {string} fieldname The fieldname compared by the condition.
 * @param {string} language The language used for the question texts.
 * @param {string} extraTitle Text added in brackets to the title, e.g. "Single checkbox".
 * @returns {void}
 */
export const createQuestion = (
  cQuestions,
  question,
  subquestion = null,
  fieldname,
  language,
  extraTitle = ''
) => {
  const baseTitle = getTextOrCode(question, language)
  let title = baseTitle

  const isRankingQuestion = question.type === getQuestionTypeInfo().RANKING.type

  if (extraTitle && (isRankingQuestion || !subquestion)) {
    title = `[${extraTitle}] ${baseTitle}`
  } else if (subquestion) {
    const subquestionText =
      getQuestionText(subquestion, language) || subquestion.title || ''
    title = `[${subquestionText}]${extraTitle ? `[${extraTitle}]` : ''} ${baseTitle}`
  }

  cQuestions.push({
    title,
    qid: question.qid,
    type: question.type,
    cfieldname: fieldname,
  })
}
