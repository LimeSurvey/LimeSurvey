import {
  addNoAnswerIfAllowed,
  createAnswer,
  createQuestion,
  getAnswerText,
} from '../helpers'
import { findFieldname } from '../utils'

export const arrayDualScaleTypeHandler = (
  question,
  language,
  cQuestions,
  cAnswers
) => {
  const subquestions = question.subquestions ?? []
  const label1 = question.attributes?.dualscale_headerA?.[language]?.trim()
    ? question.attributes.dualscale_headerA[language]
    : t('Scale 1')
  const label2 = question.attributes?.dualscale_headerB?.[language]?.trim()
    ? question.attributes.dualscale_headerB[language]
    : t('Scale 2')

  subquestions.forEach((subquestion) => {
    const fieldname0 = findFieldname({
      qid: question.qid,
      sqid: subquestion.qid,
      aid: subquestion.title,
      scaleId: 0,
    })
    const fieldname1 = findFieldname({
      qid: question.qid,
      sqid: subquestion.qid,
      aid: subquestion.title,
      scaleId: 1,
    })

    createQuestion(
      cQuestions,
      question,
      subquestion,
      fieldname0,
      language,
      `${label1}`
    )
    createQuestion(
      cQuestions,
      question,
      subquestion,
      fieldname1,
      language,
      `${label2}`
    )

    const answers = question.answers ?? []
    answers
      .filter((answer) => answer.scaleId === 0 || answer.scaleId === 1)
      .forEach((answer) => {
        const fieldname = answer.scaleId === 0 ? fieldname0 : fieldname1

        createAnswer(
          cAnswers,
          answer.code,
          `${answer.code} (${getAnswerText(answer, language)})`,
          fieldname
        )
      })

    addNoAnswerIfAllowed(cAnswers, question, fieldname0)
    addNoAnswerIfAllowed(cAnswers, question, fieldname1)
  })
}
