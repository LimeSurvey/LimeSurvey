import { NEW_OBJECT_ID_PREFIX, RandomNumber } from 'helpers'
import { cloneDeep } from 'lodash'

export const DuplicateQuestion = (question) => {
  const newDuplicatedQuestionId = `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`
  const duplicatedQuestion = cloneDeep({
    ...question,
    qid: newDuplicatedQuestionId,
    tempId: newDuplicatedQuestionId,
    // todo: make it like the core app
    title: `Q${RandomNumber()}`,
  })

  for (const [languageKey, language] of Object.entries(
    duplicatedQuestion.l10ns ?? {}
  )) {
    duplicatedQuestion.l10ns[languageKey] = {
      question: language.question,
      help: language.help,
    }
  }

  duplicatedQuestion.answers =
    duplicatedQuestion.answers?.map((answer) => {
      delete answer.aid
      const tempId = `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`

      for (const [languageKey, language] of Object.entries(
        answer.l10ns ?? {}
      )) {
        answer.l10ns[languageKey] = {
          answer: language.answer,
          language: language.language,
        }
      }

      return {
        ...answer,
        qid: newDuplicatedQuestionId,
        tempId: tempId,
        aid: tempId,
      }
    }) || []

  duplicatedQuestion.subquestions =
    duplicatedQuestion.subquestions?.map((subQuestion) => {
      const tempId = `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`
      delete subQuestion.aid

      for (const [languageKey, language] of Object.entries(
        subQuestion.l10ns ?? {}
      )) {
        subQuestion.l10ns[languageKey] = {
          question: language.question,
          language: language.language,
        }
      }

      return {
        ...subQuestion,
        parentQid: newDuplicatedQuestionId,
        tempId: tempId,
        qid: tempId,
      }
    }) || []

  return duplicatedQuestion
}
