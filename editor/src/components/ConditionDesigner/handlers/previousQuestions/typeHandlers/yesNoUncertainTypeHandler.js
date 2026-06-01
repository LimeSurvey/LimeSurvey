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
    { value: 'N', labelKey: t('No') },
    { value: 'U', labelKey: t('Uncertain') },
  ]

  createQuestion(cQuestions, question, null, fieldname, language)
  choices.forEach(({ value, labelKey }) =>
    createAnswer(cAnswers, value, labelKey, fieldname)
  )
  addNoAnswerIfAllowed(cAnswers, question, fieldname)
}
