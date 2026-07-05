import { getQuestionTypeInfo } from 'components/QuestionTypes'

import { getQuestionText } from '../helpers'

export const createQuestion = (
  cQuestions,
  question,
  subquestion = null,
  fieldname,
  language,
  extraTitle = ''
) => {
  const baseTitle = getQuestionText(question, language)
  let title = baseTitle

  if (subquestion) {
    const isRankingType = question.type === getQuestionTypeInfo().RANKING.type
    const subquestionText = getQuestionText(subquestion, language)
    const prefix = isRankingType
      ? `${t('RANK')} ${subquestion.sortOrder + 1}`
      : subquestionText
    const extra = extraTitle ? `[${extraTitle}]` : ''

    title = `[${prefix}]${extra} ${baseTitle}`
  } else if (extraTitle) {
    title = `[${extraTitle}] ${baseTitle}`
  }

  cQuestions.push({
    title,
    qid: question.qid,
    type: question.type,
    cfieldname: fieldname,
  })
}
