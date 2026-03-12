import { RemoveHTMLTagsInString } from 'helpers'

export const getQuestionText = (question, language) =>
  RemoveHTMLTagsInString(question.l10ns?.[language]?.question || '')
