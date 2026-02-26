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
    const subquestionText = getQuestionText(subquestion, language)
    title = `[${subquestionText}]${extraTitle ? `[${extraTitle}]` : ''} ${baseTitle}`
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
