import { getQuestionText } from '../helpers'
import { getQuestionTypeInfo } from '../../../../QuestionTypes/index.js'

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

  const isRankingQuestion = question.type === getQuestionTypeInfo().RANKING.type

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
