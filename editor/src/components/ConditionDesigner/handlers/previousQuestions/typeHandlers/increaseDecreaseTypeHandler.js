import { findFieldname } from '../utils'
import { addNoAnswerIfAllowed, createAnswer, createQuestion } from '../helpers'

export const increaseDecreaseTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })
  const choices = [
    { value: 'I', labelKey: t('Increase') },
    { value: 'S', labelKey: t('Same') },
    { value: 'D', labelKey: t('Decrease') },
  ]

  createQuestion(cQuestions, question, null, fieldname, language)
  choices.forEach(({ value, labelKey }) =>
    createAnswer(cAnswers, value, labelKey, fieldname)
  )
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
