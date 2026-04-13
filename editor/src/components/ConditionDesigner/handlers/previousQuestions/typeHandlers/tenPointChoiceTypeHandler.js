import { findFieldname } from '../utils'
import {
  createQuestion,
  addNoAnswerIfAllowed,
  createNumericAnswers,
} from '../helpers'

export const tenPointChoiceTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })

  createQuestion(cQuestions, question, null, fieldname, language)
  createNumericAnswers(cAnswers, 10, 1, fieldname)
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
