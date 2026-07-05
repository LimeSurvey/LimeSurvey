import {
  addNoAnswerIfAllowed,
  createAnswer,
  createQuestion,
  getQuestionText,
} from '../helpers'
import { findFieldname } from '../utils'

export const rankingTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const subquestions = question.subquestions

  const rankingAnswers = subquestions.map((subquestion) => ({
    value: subquestion.title,
    label: `${subquestion.title} (${getQuestionText(subquestion, language)})`,
  }))

  for (let j = 0; j < subquestions.length; j++) {
    const rankingPos = j + 1
    const fieldname = findFieldname({
      qid: question.qid,
      sqid: subquestions[j].qid,
      aid: subquestions[j].title,
    })

    createQuestion(
      cQuestions,
      question,
      subquestions[j],
      fieldname,
      language,
      `${t('RANK')} ${rankingPos}`
    )

    rankingAnswers.forEach(({ value, label }) => {
      createAnswer(cAnswers, value, label, fieldname)
    })

    addNoAnswerIfAllowed(cAnswers, question, fieldname)
  }
}
