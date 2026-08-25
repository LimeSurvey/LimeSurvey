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
  const rankingItems = question.subquestions ?? []

  const rankingAnswers = rankingItems.map((rankingItem) => ({
    value: rankingItem.title,
    label: `${rankingItem.title} (${getQuestionText(rankingItem, language)})`,
  }))

  rankingItems.forEach((rankingItem, index) => {
    const rankPos = index + 1

    const fieldname = findFieldname({
      qid: question.qid,
      sqid: rankingItem.qid,
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
