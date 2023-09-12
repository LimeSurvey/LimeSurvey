import { LANGUAGE_CODES, RandomNumber } from 'helpers'

export const DuplicateQuestion = (question, questions, index) => {
  const languages = Object.values(LANGUAGE_CODES)
  const newDuplicatedQuestionId = question.qid + RandomNumber()
  const duplicatedQuestion = {
    ...question,
    l10ns: {
      ...question.l10ns,
      ...languages.reduce((l10ns, language) => {
        if (!question.l10ns[language]) {
          return {
            ...l10ns,
          }
        }

        return {
          ...l10ns,
          [language]: {
            ...question.l10ns[language],
            question: `${question.l10ns[language].question}`,
            qid: newDuplicatedQuestionId,
          },
        }
      }, {}),
    },
    qid: newDuplicatedQuestionId,
  }

  let updatedQuestions = [...questions]

  updatedQuestions = updatedQuestions.map((question) => ({
    ...question,
    qid: question.qid + RandomNumber(),
  }))

  if (question.attributes) {
    question.answers = question.answers.map((answer) => {
      return {
        ...answer,
        qid: newDuplicatedQuestionId,
        aid: RandomNumber(),
      }
    })
  }

  duplicatedQuestion.answers = duplicatedQuestion.answers
    ? duplicatedQuestion.answers.map((answer) => {
        return {
          ...answer,
          qid: newDuplicatedQuestionId,
          aid: RandomNumber(),
        }
      })
    : []

  duplicatedQuestion.attributes = duplicatedQuestion.attributes
    ? Object.keys(duplicatedQuestion.attributes).map((attributeKey) => {
        return {
          ...duplicatedQuestion.attributes[attributeKey],
          qaid: newDuplicatedQuestionId,
        }
      })
    : {}

  duplicatedQuestion.subquestions = duplicatedQuestion.subquestions
    ? duplicatedQuestion.subquestions.map((subQuestion) => {
        const subQuestionid = RandomNumber()

        return {
          ...subQuestion,
          parentQid: newDuplicatedQuestionId,
          qid: subQuestionid,
          l10ns: {
            ...subQuestion.l10ns,
            ...languages.reduce((l10ns, language) => {
              return {
                ...l10ns,
                [language]: {
                  ...subQuestion.l10ns[language],
                  qid: subQuestionid,
                },
              }
            }, {}),
          },
        }
      })
    : []

  updatedQuestions.splice(index, 0, duplicatedQuestion)

  updatedQuestions = updatedQuestions.map((question, index) => {
    question.questionOrder = index + 1
    return question
  })

  return {
    updatedQuestions,
    duplicatedQuestion,
  }
}
