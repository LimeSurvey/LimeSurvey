import { addNoAnswerIfAllowed, createQuestion } from '../helpers'
import { findFieldname } from '../utils'

export const multipleInputsTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const subquestions = question.subquestions ?? []

  for (let j = subquestions.length - 1; j >= 0; j--) {
    const fieldname = findFieldname({
      qid: question.qid,
      sqid: subquestions[j].qid,
      aid: subquestions[j].title,
    })
    createQuestion(cQuestions, question, subquestions[j], fieldname, language)
    addNoAnswerIfAllowed(cAnswers, question, fieldname)
  }
}
