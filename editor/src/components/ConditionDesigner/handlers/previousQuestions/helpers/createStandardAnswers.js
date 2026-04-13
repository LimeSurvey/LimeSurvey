import { createAnswer, getAnswerText } from '../helpers'

export const createStandardAnswers = (
  cAnswers,
  question,
  subquestion,
  answers,
  fieldname,
  language
) => {
  for (const answer of answers) {
    createAnswer(
      cAnswers,
      answer.code,
      `${answer.code} (${getAnswerText(answer, language)})`,
      fieldname
    )
  }
}
