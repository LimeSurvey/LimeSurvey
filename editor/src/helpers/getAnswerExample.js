import { NEW_OBJECT_ID_PREFIX, SCALE_1 } from 'helpers'
import { RandomNumber } from './RandomNumber'

export const getAnswerExample = ({
  qid,
  aid,
  sortOrder = 0,
  scaleId = SCALE_1,
  languages = [],
  languageValue = '',
  code = `A${RandomNumber(1, 999)}`,
}) => {
  aid = aid ?? `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`
  qid = qid ?? `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`

  return {
    aid,
    tempId: aid,
    qid,
    code,
    sortOrder,
    assessmentValue: 0,
    scaleId,
    l10ns: languages.reduce((acc, language) => {
      acc[language] = {
        aid: aid,
        answer: languageValue,
        language,
      }

      return acc
    }, {}),
  }
}
