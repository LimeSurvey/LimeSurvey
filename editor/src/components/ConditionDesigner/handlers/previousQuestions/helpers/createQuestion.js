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
 * @param {string} type The question type deciding how the field is compared, defaults to the type of the question.
 * @returns {void}
 */
export const createQuestion = (
  cQuestions,
  question,
  subquestion = null,
  fieldname,
  language,
  extraTitle = '',
  type = question.type
) => {
  const baseTitle = getQuestionText(question, language)
  let title = baseTitle

  const isRankingQuestion = question.type === getQuestionTypeInfo().RANKING.type

  if (extraTitle && (isRankingQuestion || !subquestion)) {
    title = `[${extraTitle}] ${baseTitle}`
  } else if (subquestion) {
    const subquestionText = getQuestionText(subquestion, language)
    title = `[${subquestionText}]${extraTitle ? `[${extraTitle}]` : ''} ${baseTitle}`
  }

  cQuestions.push({
    title,
    qid: question.qid,
    type,
    cfieldname: fieldname,
  })
}
