import { Entities } from './Buffer/Entities'
import { EntitiesType } from './Buffer/EntitiesType'
import {
  CONDITION_ACTIONS,
  isConditionOperationWaiting,
} from './Buffer/operationsHandlers/index.js'
import { updateSurveyEntitiesData } from './entityHelpers'

/**
 * Whether the operation changes the question conditions (scenarios),
 * which means the server will regenerate the question relevance.
 * Expression script operations set the relevance directly, so they are excluded.
 */
export const isConditionLogicOperation = (operation) =>
  operation?.entity === Entities.questionCondition &&
  operation?.props?.action !== CONDITION_ACTIONS.SCRIPT

// Question IDs of the condition operations (without error) matching `predicate`.
const getConditionOperationQids = (operations, predicate) => [
  ...new Set(
    (operations ?? [])
      .filter((operation) => operation?.entity === Entities.questionCondition)
      .filter((operation) => !operation.error && predicate(operation))
      .map((operation) => operation.props?.qid ?? operation.qid)
      .filter((qid) => qid !== undefined && qid !== null && qid !== '')
      .map(String)
  ),
]

export const getConditionLogicQids = (operations = []) =>
  getConditionOperationQids(operations, isConditionLogicOperation)

export const hasPendingConditionLogicOperations = (operations, qid) =>
  getConditionLogicQids(operations).includes(String(qid))

/**
 * Question IDs with any condition operation (scenarios or expression script)
 * still waiting in the buffer: A relevance returned by the server for these
 * questions is outdated.
 */
export const getQidsWithPendingConditionOperations = (operations = []) =>
  getConditionOperationQids(operations, () => true)

/**
 * Applies the question relevances returned by the survey PATCH response
 * (extras.questionRelevance, keyed by qid), except for the questions with
 * newer condition operations (or an expression script) still to be sent:
 * Their returned relevance is already outdated.
 * Operations waiting for a temp ID don't count: They may wait for a while, and
 * meanwhile the relevance of the saved conditions is the right one.
 *
 * @param {{survey: object, operationsBuffer: object}} mapResult
 * @param {Object<string, string>} questionRelevance qid => relevance
 * @returns {{survey: object, operationsBuffer: object}}
 */
export const applyQuestionRelevance = (mapResult, questionRelevance) => {
  const pendingConditionQids = getQidsWithPendingConditionOperations(
    mapResult.operationsBuffer
      .getOperations()
      .filter((operation) => !isConditionOperationWaiting(operation))
  )

  return Object.entries(questionRelevance ?? {})
    .filter(([qid]) => !pendingConditionQids.includes(String(qid)))
    .reduce(
      (result, [qid, relevance]) =>
        updateSurveyEntitiesData(
          EntitiesType.question,
          result.survey,
          result.operationsBuffer,
          { qid: Number(qid), relevance }
        ),
      mapResult
    )
}
