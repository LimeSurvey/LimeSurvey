import { isTrue, OTHER_CODE } from 'helpers'

import { createAnswer } from './createAnswer'

/**
 * Adds the "Other" answer option of a single choice question with "Other" enabled.
 *
 * @param {object[]} cAnswers The condition answers list, extended in place.
 * @param {object} question The single choice question.
 * @param {string} fieldname The fieldname of the question.
 * @returns {void}
 */
export const addOtherAnswerIfEnabled = (cAnswers, question, fieldname) => {
  if (isTrue(question.other)) {
    createAnswer(cAnswers, OTHER_CODE, t('Other'), fieldname)
  }
}
