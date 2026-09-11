import { RemoveHTMLTagsInString } from 'helpers'

export const getAnswerText = (answer, language) =>
  RemoveHTMLTagsInString(answer.l10ns?.[language]?.answer || answer.code)
