import { format } from 'util'

import { getAttributeValue } from 'helpers'

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

  // Like the server (createFieldMap), max_subquestions caps the number of rank
  // slots (response columns), while every item can still be ranked.
  const maxRankSlots = parseInt(
    getAttributeValue(question.attributes?.max_subquestions),
    10
  )
  const rankSlots =
    maxRankSlots > 0 && maxRankSlots < rankingItems.length
      ? rankingItems.slice(0, maxRankSlots)
      : rankingItems

  const rankingAnswers = rankingItems.map((rankingItem) => ({
    value: rankingItem.title,
    label: `${rankingItem.title} (${getQuestionText(rankingItem, language)})`,
  }))

  rankSlots.forEach((rankingItem, index) => {
    const rankPos = index + 1

    const fieldname = findFieldname({
      qid: question.qid,
      sqid: rankingItem.qid,
    })

    createQuestion(
      cQuestions,
      question,
      rankingItem,
      fieldname,
      language,
      format(t('Rank %s'), rankPos)
    )

    rankingAnswers.forEach(({ value, label }) => {
      createAnswer(cAnswers, value, label, fieldname)
    })

    addNoAnswerIfAllowed(cAnswers, question, fieldname)
  })
}
