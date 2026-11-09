import { isEqual } from 'lodash'

import { QUESTION_RELEVANCE_DEFAULT_VALUE } from './constants'
import {
  getConditionTempIds,
  getKnownTempIds,
  removeConditionOpsWithUnknownTempIds,
  usesUnknownTempId,
} from './Buffer/operationsHandlers/handleQuestionConditionOperation/handleQuestionConditionOperation'

/**
 * Removes the conditions matching `isRemoved` from the questions. Questions
 * left without scenarios get the default relevance back.
 * Doesn't mutate `questionGroups`: the changed groups and questions are
 * copied, the others are kept as is.
 *
 * @param {Array} questionGroups
 * @param {function(object): boolean} isRemoved
 * @returns {{questionGroups: Array, changedQids: Array<string>}}
 */
const removeConditions = (questionGroups = [], isRemoved) => {
  const changedQids = []

  const updatedGroups = questionGroups.map((group) => {
    let groupChanged = false
    const questions = (group.questions ?? []).map((question) => {
      const hasRemoved = (question.scenarios ?? []).some((scenario) =>
        (scenario.conditions ?? []).some(isRemoved)
      )
      if (!hasRemoved) return question

      groupChanged = true
      changedQids.push(String(question.qid))
      const scenarios = question.scenarios
        .map((scenario) => ({
          ...scenario,
          conditions: (scenario.conditions ?? []).filter((c) => !isRemoved(c)),
        }))
        .filter((scenario) => scenario.conditions.length)

      return {
        ...question,
        scenarios,
        relevance: scenarios.length
          ? question.relevance
          : QUESTION_RELEVANCE_DEFAULT_VALUE,
      }
    })

    return groupChanged ? { ...group, questions } : group
  })

  return changedQids.length
    ? { questionGroups: updatedGroups, changedQids }
    : { questionGroups, changedQids }
}

/**
 * Removes the conditions using as source an unsaved entity that doesn't exist
 * anymore (see usesUnknownTempId), e.g. a deleted unsaved subquestion. Their
 * buffered operations are dropped too (removeConditionOpsWithUnknownTempIds),
 * so they would otherwise be shown but never saved.
 * Questions left without scenarios get the default relevance back.
 * Doesn't mutate `survey`: the changed groups and questions are copied.
 *
 * @param {object} survey
 * @param {Set<string>} knownTempIds
 * @returns {{survey: object, changedQids: Array<string>}}
 */
export const removeConditionsWithUnknownTempIds = (survey, knownTempIds) => {
  if (!survey?.questionGroups) return { survey, changedQids: [] }

  const { questionGroups, changedQids } = removeConditions(
    survey.questionGroups,
    (condition) => usesUnknownTempId(condition, knownTempIds)
  )

  return changedQids.length
    ? { survey: { ...survey, questionGroups }, changedQids }
    : { survey, changedQids }
}

/**
 * Removes, from the survey and from the buffered operations, the conditions of
 * or using as source an unsaved question, subquestion or group that was
 * deleted (see removeConditionsWithUnknownTempIds and
 * removeConditionOpsWithUnknownTempIds): its temp ID will never be replaced.
 * Usually no condition uses a temp ID, and nothing more is computed.
 *
 * @param {object} survey
 * @param {Array} operations the buffered operations (not mutated)
 * @returns {{survey: object, changedQids: Array<string>, operations: Array}}
 *   `survey` and `operations` are the given ones when unchanged
 */
export const removeConditionsWithUnknownTempIdsFromSurveyAndBuffer = (
  survey,
  operations = []
) => {
  const unchanged = { survey, changedQids: [], operations }
  if (!survey?.questionGroups) return unchanged

  const conditionTempIds = getConditionTempIds(operations, survey)
  if (!conditionTempIds.size) return unchanged

  // Only the temp IDs used by the conditions are looked for.
  const knownTempIds = getKnownTempIds(survey, operations, conditionTempIds)
  const cleanedOperations = removeConditionOpsWithUnknownTempIds(
    operations,
    knownTempIds
  )

  return {
    ...removeConditionsWithUnknownTempIds(survey, knownTempIds),
    operations: isEqual(cleanedOperations, operations)
      ? operations
      : cleanedOperations,
  }
}
