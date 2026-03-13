import {
  createQuestion,
  addNoAnswerIfAllowed,
  createNumericAnswers,
} from '../helpers'
import { findFieldname } from '../utils'

export const fivePointChoiceTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })

  createQuestion(cQuestions, question, null, fieldname, language)
  createNumericAnswers(cAnswers, 5, 1, fieldname)
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
