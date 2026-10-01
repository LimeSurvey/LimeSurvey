import { createAnswer, createQuestion } from '../helpers'

import { findFieldname } from '../utils'

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
}
