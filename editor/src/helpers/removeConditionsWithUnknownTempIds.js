import { isEqual } from 'lodash'

import { QUESTION_RELEVANCE_DEFAULT_VALUE } from './constants'
import {
  getConditionTempIds,
  getKnownTempIds,
  removeConditionOpsWithUnknownTempIds,
  usesUnknownTempId,
} from './Buffer/operationsHandlers/handleQuestionConditionOperation/handleQuestionConditionOperation'

/**
 * Removes from the questions of the survey the conditions using an unknown
 * temp ID (see usesUnknownTempId). Questions left without scenarios get the
 * default relevance back.
 * Doesn't mutate `survey`: Only the changed groups and questions are copied.
 *
 * @param {object} survey
 * @param {Set<string>} knownTempIds
 * @returns {{survey: object, changedQids: Array<string>}}
 */
const removeSurveyConditionsWithUnknownTempIds = (survey, knownTempIds) => {
  const isRemoved = (condition) => usesUnknownTempId(condition, knownTempIds)
  const changedQids = []

  const questionGroups = survey.questionGroups.map((group) => {
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
    ? { survey: { ...survey, questionGroups }, changedQids }
    : { survey, changedQids }
}

/**
 * Removes, from the survey and from the buffered operations, the conditions of
 * or using as source an unsaved question, subquestion or group that was
 * deleted (see usesUnknownTempId and removeConditionOpsWithUnknownTempIds):
 * its temp ID will never be replaced.
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
  // The usual case: Every unsaved entity they use still exists.
  if ([...conditionTempIds].every((id) => knownTempIds.has(id))) {
    return unchanged
  }

  const cleanedOperations = removeConditionOpsWithUnknownTempIds(
    operations,
    knownTempIds
  )

  return {
    ...removeSurveyConditionsWithUnknownTempIds(survey, knownTempIds),
    operations: isEqual(cleanedOperations, operations)
      ? operations
      : cleanedOperations,
  }
}
