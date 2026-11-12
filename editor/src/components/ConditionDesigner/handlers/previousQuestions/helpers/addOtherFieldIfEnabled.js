import { isTrue } from 'helpers'

import { findFieldname } from '../utils'
import { createAnswer } from './createAnswer'
import { createQuestion } from './createQuestion'

/**
 * Adds the "Other" text field of a multiple choice question with "Other"
 * enabled as a separate condition question.
 * Unlike the subquestion checkboxes it is not flagged with isCheckboxField: It
 * is compared like a text field, only "No answer" is predefined.
 *
 * @param {object[]} cQuestions The condition questions list, extended in place.
 * @param {object[]} cAnswers The condition answers list, extended in place.
 * @param {object} question The multiple choice question.
 * @param {string} language The language used for the question text.
 * @returns {void}
 */
export const addOtherFieldIfEnabled = (
  cQuestions,
  cAnswers,
  question,
  language
) => {
  if (!isTrue(question.other)) {
    return
  }

  const fieldname = findFieldname({ qid: question.qid, aid: 'other' })
  if (!fieldname) {
    return
  }

  createQuestion(cQuestions, question, null, fieldname, language, t('Other'))
  createAnswer(cAnswers, '', t('No answer'), fieldname)
}
