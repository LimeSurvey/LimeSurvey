import {
  addNoAnswerIfAllowed,
  addOtherAnswerIfEnabled,
  createQuestion,
  createStandardAnswers,
} from '../helpers'
import { findFieldname } from '../utils'

/**
 * Adds the condition question of a single choice question with its answer
 * options, the "Other" option when enabled and "No answer" when allowed.
 *
 * @param {object} question The single choice question.
 * @param {string} language The language used for the question and answer texts.
 * @param {object[]} cQuestions The condition questions list, extended in place.
 * @param {object[]} cAnswers The condition answers list, extended in place.
 * @returns {void}
 */
export const singleChoiceTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })

  createQuestion(cQuestions, question, null, fieldname, language)

  createStandardAnswers(
    cAnswers,
    question,
    null,
    question.answers ?? [],
    fieldname,
    language
  )
  addOtherAnswerIfEnabled(cAnswers, question, fieldname)
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
