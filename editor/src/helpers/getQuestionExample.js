import { NEW_OBJECT_ID_PREFIX, SCALE_1 } from 'helpers'
import { RandomNumber } from './RandomNumber'

export const getQuestionExample = ({
  gid,
  qid,
  sid,
  parentQid = 0,
  questionThemeName = 'longfreetext',
  type = 'T',
  sortOrder = 1, // Questions starts from 1, but subquestions starts from 0
  scaleId = SCALE_1,
  title = `Q${RandomNumber(1, 999)}`,
  answers = [],
  subquestions = [],
  attributes = [],
  mandatory = false,
  encrypted = false,
  relevance = '1',
  languages = [],
  scenarios = [],
}) => {
  qid = qid ?? `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`

  return {
    sid,
    qid,
    tempId: qid,
    gid,
    type,
    scaleId,
    sortOrder,
    questionThemeName,
    parentQid,
    title: title ? title : `${parentQid ? 'SQ' : 'Q'}${RandomNumber(1, 999)}`,
    preg: null,
    other: false,
    mandatory,
    encrypted,
    moduleName: null,
    sameDefault: null,
    relevance,
    sameScript: null,
    l10ns: languages.reduce((acc, language) => {
      acc[language] = {
        qid,
        question: '',
        language,
      }

      return acc
    }, {}),
    attributes,
    answers,
    subquestions,
    scenarios,
  }
}
