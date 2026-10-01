import { RemoveHTMLTagsInString } from 'helpers'

import { addNoAnswerIfAllowed, createAnswer, createQuestion } from '../helpers'
import { findFieldname } from '../utils'

export const defaultTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })
  createQuestion(cQuestions, question, null, fieldname, language)

  const answers = question.answers ?? []
  for (let k = 0; k < answers.length; k++) {
    createAnswer(
      cAnswers,
      answers[k].code,
      `${answers[k].code} (${RemoveHTMLTagsInString(answers[k].l10ns?.[language]?.answer || answers[k].code)})`,
      fieldname
    )
  }
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
