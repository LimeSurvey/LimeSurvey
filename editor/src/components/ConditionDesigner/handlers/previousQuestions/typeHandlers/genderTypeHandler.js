import { addNoAnswerIfAllowed, createAnswer, createQuestion } from '../helpers'
import { findFieldname } from '../utils'

export const genderTypeHandler = (question, language, cQuestions, cAnswers) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })

  createQuestion(cQuestions, question, null, fieldname, language)
  createAnswer(cAnswers, 'F', t('Female (F)'), fieldname)
  createAnswer(cAnswers, 'M', t('Male (M)'), fieldname)
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
