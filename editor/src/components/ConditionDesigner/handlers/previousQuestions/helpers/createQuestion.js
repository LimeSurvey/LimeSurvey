import { getQuestionTypeInfo } from 'components/QuestionTypes/index.js'

import { getQuestionText } from '../helpers'

// Read once: getQuestionTypeInfo() builds (and translates) every type, and
// this runs for each source question of the Condition Designer.
let rankingType
const getRankingType = () => {
  rankingType ??= getQuestionTypeInfo().RANKING.type
  return rankingType
}

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

  const isRankingQuestion = question.type === getRankingType()

  if (extraTitle && (isRankingQuestion || !subquestion)) {
    title = `[${extraTitle}] ${baseTitle}`
  } else if (subquestion) {
    const subquestionText = getQuestionText(subquestion, language)
    title = `[${subquestionText}]${extraTitle ? `[${extraTitle}]` : ''} ${baseTitle}`
  }

  cQuestions.push({
    title,
    qid: question.qid,
    type: question.type,
    cfieldname: fieldname,
  })
}
