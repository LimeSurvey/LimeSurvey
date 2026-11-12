import {
  addOtherFieldIfEnabled,
  createAnswer,
  createQuestion,
} from '../helpers'

import { findFieldname } from '../utils'

/**
 * Adds a "Single checkbox" condition question for every subquestion of a
 * multiple choice question, and one for its "Other" text field when enabled.
 *
 * @param {object} question The multiple choice question.
 * @param {string} language The language used for the question texts.
 * @param {object[]} cQuestions The condition questions list, extended in place.
 * @param {object[]} cAnswers The condition answers list, extended in place.
 * @returns {void}
 */
export const multipleChoiceTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const subquestions = question.subquestions ?? []

  for (let j = subquestions.length - 1; j >= 0; j--) {
    const fieldname =
      '+' +
      findFieldname({
        qid: question.qid,
        sqid: subquestions[j].qid,
        aid: subquestions[j].title,
      })

    createQuestion(
      cQuestions,
      question,
      subquestions[j],
      fieldname,
      language,
      `Single checkbox`
    )
    createAnswer(cAnswers, 'Y', t('Checked (Y)'), fieldname)
    createAnswer(cAnswers, '', t('Not checked'), fieldname)
  }

  addOtherFieldIfEnabled(cQuestions, cAnswers, question, language)
}
