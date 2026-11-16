import { addNoAnswerIfAllowed, createAnswer, createQuestion } from '../helpers'
import { findFieldname } from '../utils'

export const yesNoUncertainTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const fieldname = findFieldname({
    qid: question.qid,
  })
  const choices = [
    { value: 'Y', labelKey: t('Yes') },
    { value: 'U', labelKey: t('Uncertain') },
    { value: 'N', labelKey: t('No') },
  ]

  createQuestion(cQuestions, question, null, fieldname, language)
  choices.forEach(({ value, labelKey }) =>
    createAnswer(cAnswers, value, labelKey, fieldname)
  )
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
