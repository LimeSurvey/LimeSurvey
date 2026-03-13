import {
  addNoAnswerIfAllowed,
  createQuestion,
  createStandardAnswers,
} from '../helpers'
import { findFieldname } from '../utils'

export const dateTimeTypeHandler = (
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
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
