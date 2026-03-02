import {
  addNoAnswerIfAllowed,
  createAnswer,
  createQuestion,
  getAnswerText,
} from '../helpers'
import { findFieldname } from '../utils'

export const rankingTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const answers = question.answers

  const rankingAnswers = answers.map((answer) => ({
    value: answer.code,
    label: `${answer.code} (${getAnswerText(answer, language)})`,
  }))

  answers.forEach((answer, index) => {
    const rankPos = index + 1

    const fieldname = findFieldname({
      qid: question.qid,
      aid: answer.aid,
    })

    createQuestion(
      cQuestions,
      question,
      null,
      fieldname,
      language,
      `${t('RANK')} ${rankPos}`
    )

    rankingAnswers.forEach(({ value, label }) => {
      createAnswer(cAnswers, value, label, fieldname)
    })

    addNoAnswerIfAllowed(cAnswers, question, fieldname)
  })
}
