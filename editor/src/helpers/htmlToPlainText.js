import { RemoveHTMLTagsInString } from './RemoveHTMLTagsInString'
import { decodeHTMLEntities } from './decodeHTMLEntities'

export const htmlToPlainText = (string) =>
  decodeHTMLEntities(RemoveHTMLTagsInString(string))
