import { SOFT_MANDATORY } from 'helpers'

import { questionTypeRequiresBlankNoAnswer } from './questionTypeRequiresBlankNoAnswer'

export const addNoAnswerIfAllowed = (cAnswers, question, fieldname) => {
  const value = questionTypeRequiresBlankNoAnswer(question.type) ? '' : ' '
  const isOptional =
    question.mandatory !== true && question.mandatory !== SOFT_MANDATORY

  if (isOptional) {
    cAnswers.push({
      cfieldname: fieldname,
      value,
      label: t('No answer'),
    })
  }
}
