import { isTrue } from 'helpers'
import { getQuestionTypeInfo } from 'components/QuestionTypes/index.js'

import { findFieldname } from '../utils'
import { createAnswer } from './createAnswer'
import { createQuestion } from './createQuestion'

/**
 * Adds the "Other" text field of a multiple choice question with "Other"
 * enabled as a separate condition question.
 * It is compared like a short text question: Only "No answer" is predefined.
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

  createQuestion(
    cQuestions,
    question,
    null,
    fieldname,
    language,
    t('Other'),
    getQuestionTypeInfo().SHORT_TEXT.type
  )
  createAnswer(cAnswers, '', t('No answer'), fieldname)
}
