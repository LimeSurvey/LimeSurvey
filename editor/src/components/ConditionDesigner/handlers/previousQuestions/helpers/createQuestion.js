import { format } from 'util'

import { getQuestionTypeInfo } from 'components/QuestionTypes/index.js'

import { getQuestionText } from '../helpers'

/**
 * Adds a condition question for a question or one of its subquestions.
 *
 * @param {object[]} cQuestions The condition questions list, extended in place.
 * @param {object} question The question.
 * @param {object|null} subquestion The subquestion, or null for the question itself.
 * @param {string} fieldname The fieldname compared by the condition.
 * @param {string} language The language used for the question texts.
 * @param {string} extraTitle Text added in brackets to the title, e.g. "Single checkbox".
 * @param {object} extraProps Additional properties of the condition question, e.g. isCheckboxField. They can't overwrite the core properties.
 * @returns {void}
 */
export const createQuestion = (
  cQuestions,
  question,
  subquestion = null,
  fieldname,
  language,
  extraTitle = '',
  extraProps = {}
) => {
  const baseTitle = getQuestionText(question, language)
  let title = baseTitle

  const isRankingQuestion = question.type === getQuestionTypeInfo().RANKING.type

  // Translatable patterns, so translators can adapt the brackets and spacing
  if (extraTitle && (isRankingQuestion || !subquestion)) {
    title = format(t('[%s] %s'), extraTitle, baseTitle)
  } else if (subquestion) {
    const subquestionText = getQuestionText(subquestion, language)
    title = extraTitle
      ? format(t('[%s][%s] %s'), subquestionText, extraTitle, baseTitle)
      : format(t('[%s] %s'), subquestionText, baseTitle)
  }

  cQuestions.push({
    ...extraProps,
    title,
    qid: question.qid,
    type: question.type,
    cfieldname: fieldname,
  })
}
