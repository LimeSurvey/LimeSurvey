import { NEW_OBJECT_ID_PREFIX, RandomNumber } from 'helpers'
import { getScenarioConditions } from 'helpers/Buffer/operationsHandlers'
import { cloneDeep } from 'lodash'

const getConditions = (question) => getScenarioConditions(question?.scenarios)

/**
 * Whether the question still shows conditions copied from the question it
 * duplicates (see DuplicateQuestion). They carry the condition IDs of the
 * original: Changing or deleting one would change the original's condition.
 * The survey is reloaded once the duplicate was saved, which brings its own
 * conditions.
 * @param {object} question
 * @returns {boolean}
 */
export const hasCopiedConditions = (question) =>
  getConditions(question).some((condition) => condition.isCopy)

/**
 * A copy of the question with temp IDs, to be created by the server.
 * Its conditions keep the condition IDs of the original: The server copies
 * the conditions with these IDs to the new question, under new IDs the
 * response doesn't return. They are flagged `isCopy` (see hasCopiedConditions).
 * @param {object} question
 * @returns {object}
 */
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

  getConditions(duplicatedQuestion).forEach((condition) => {
    condition.isCopy = true
  })

  return duplicatedQuestion
}
