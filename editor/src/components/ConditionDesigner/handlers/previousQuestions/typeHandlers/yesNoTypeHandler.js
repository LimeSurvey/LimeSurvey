import { findFieldname } from '../utils'
import { addNoAnswerIfAllowed, createAnswer, createQuestion } from '../helpers'

export const yesNoTypeHandler = (question, language, cQuestions, cAnswers) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })

  createQuestion(cQuestions, question, null, fieldname, language)
  createAnswer(cAnswers, 'Y', t('Yes'), fieldname)
  createAnswer(cAnswers, 'N', t('No'), fieldname)
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
