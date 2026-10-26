import { createQuestion, getQuestionText } from '../helpers'
import { findFieldname } from '../utils'

export const arrayNumbersTextsTypeHandler = (
  question,
  language,
  cQuestions
) => {
  const xAxis = {}

  ;(question.subquestions ?? [])
    .filter((sq) => sq.scaleId === 1)
    .forEach((sq) => {
      xAxis[sq.title] = getQuestionText(sq, language)
    })
  ;(question.subquestions ?? [])
    .filter((sq) => sq.scaleId === 0)
    .forEach((sq) => {
      Object.entries(xAxis).forEach(([key, value]) => {
        const fieldname = findFieldname({
          qid: question.qid,
          sqid: sq.qid,
          aid: sq.title + '_' + key,
        })
        createQuestion(
          cQuestions,
          question,
          sq,
          fieldname,
          language,
          `${value}`
        )
      })
    })
}
