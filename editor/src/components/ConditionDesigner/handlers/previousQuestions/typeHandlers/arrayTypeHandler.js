import {
  addNoAnswerIfAllowed,
  createQuestion,
  createStandardAnswers,
} from '../helpers'
import { findFieldname } from '../utils'

export const arrayTypeHandler = (question, language, cQuestions, cAnswers) => {
  const subquestions = question.subquestions ?? []

  for (let j = 0; j < subquestions.length; j++) {
    const fieldname = findFieldname({
      qid: question.qid,
      sqid: subquestions[j].qid,
      aid: subquestions[j].title,
    })
    createQuestion(cQuestions, question, subquestions[j], fieldname, language)
    createStandardAnswers(
      cAnswers,
      question,
      subquestions[j],
      question.answers ?? [],
      fieldname,
      language
    )
    addNoAnswerIfAllowed(cAnswers, question, fieldname)
  }
}
