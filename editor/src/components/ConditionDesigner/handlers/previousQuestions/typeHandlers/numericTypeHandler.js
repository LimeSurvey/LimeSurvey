import { findFieldname } from '../utils'
import { addNoAnswerIfAllowed, createQuestion } from '../helpers'

export const numericTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })

  createQuestion(cQuestions, question, null, fieldname, language)
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
