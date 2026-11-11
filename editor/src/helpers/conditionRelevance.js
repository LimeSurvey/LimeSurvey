import { Entities } from './Buffer/Entities'
import {
  CONDITION_ACTIONS,
  getOperationQid,
  isConditionOperationOfQuestion,
  isConditionOperationWaiting,
} from './Buffer/operationsHandlers/index.js'
import { getQuestionById } from './getQuestionById'

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
      .map(getOperationQid)
      .filter((qid) => qid !== undefined && qid !== null && qid !== '')
      .map(String)
  ),
]

export const getConditionLogicQids = (operations = []) =>
  getConditionOperationQids(operations, isConditionLogicOperation)

export const hasPendingConditionLogicOperations = (operations, qid) =>
  getConditionLogicQids(operations).includes(String(qid))

/**
 * Whether the question has condition operations (not an expression script)
 * still to be sent, so the server will send a new relevance for it.
 * Operations waiting for a temp ID don't count: They may wait for a while.
 * @param {Array} operations the buffered operations
 * @param {number|string} qid
 * @returns {boolean}
 */
export const hasConditionLogicOperationsToSend = (operations, qid) =>
  (operations ?? []).some(
    (operation) =>
      isConditionOperationOfQuestion(operation, qid) &&
      isConditionLogicOperation(operation) &&
      !operation.error &&
      !isConditionOperationWaiting(operation)
  )

/**
 * Question IDs with any condition operation (scenarios or expression script)
 * still waiting in the buffer: A relevance returned by the server for these
 * questions is outdated.
 */
export const getQidsWithPendingConditionOperations = (operations = []) =>
  getConditionOperationQids(operations, () => true)

// The relevance returned by the server that were not applied, because newer
// condition operations of their question were still to be sent (qid =>
// relevance). When these operations are canceled locally instead of being
// sent, no later response brings the relevance again: It is applied then
// (see applySkippedQuestionRelevance).
const skippedRelevance = new Map()

/**
 * Forget the skipped relevance, e.g. when switching to another survey.
 * @returns {void}
 */
export const clearSkippedQuestionRelevance = () => skippedRelevance.clear()

/**
 * Forget the skipped relevance of the questions whose condition operations
 * are being sent: The server then has newer conditions or a newer expression
 * script, and the skipped relevance must not be applied over them.
 * @param {Array} sentOperations the operations of the request
 * @returns {void}
 */
export const forgetSkippedQuestionRelevance = (sentOperations = []) =>
  getQidsWithPendingConditionOperations(sentOperations).forEach((qid) =>
    skippedRelevance.delete(qid)
  )

// Sets the relevance of a question of the survey, in place.
const setQuestionRelevance = (result, qid, relevance) => {
  const { question } = getQuestionById(qid, result.survey)
  if (question) question.relevance = relevance
  return result
}

// The questions with condition operations still to be sent. Operations
// waiting for a temp ID don't count: They may wait for a while.
const getQidsWithConditionOperationsToSend = (operations) =>
  getQidsWithPendingConditionOperations(
    operations.filter((operation) => !isConditionOperationWaiting(operation))
  )

/**
 * Applies the relevance skipped by applyQuestionRelevance whose question has
 * no condition operation left to send: The newer operations were canceled
 * locally, so the skipped relevance is the one of the saved conditions.
 * Call it when no save is running. The survey is updated in place.
 *
 * @param {object} survey
 * @param {Array} operations the buffered operations
 * @returns {Array<string>} the qids whose relevance was applied
 */
export const applySkippedQuestionRelevance = (survey, operations = []) => {
  if (!skippedRelevance.size || !survey?.questionGroups) return []

  const pendingQids = getQidsWithConditionOperationsToSend(operations)
  const appliedQids = [...skippedRelevance.keys()].filter(
    (qid) => !pendingQids.includes(qid)
  )
  appliedQids.forEach((qid) => {
    setQuestionRelevance({ survey }, qid, skippedRelevance.get(qid))
    skippedRelevance.delete(qid)
  })

  return appliedQids
}

/**
 * Applies the question relevances returned by the survey PATCH response
 * (extras.questionRelevance, keyed by qid), except for the questions with
 * newer condition operations (or an expression script) still to be sent:
 * Their returned relevance is already outdated.
 * Operations waiting for a temp ID don't count: They may wait for a while, and
 * meanwhile the relevance of the saved conditions is the right one.
 * A skipped relevance is remembered (see applySkippedQuestionRelevance).
 *
 * @param {{survey: object, operationsBuffer: object}} mapResult
 * @param {Object<string, string>} questionRelevance qid => relevance
 * @returns {{survey: object, operationsBuffer: object}}
 */
export const applyQuestionRelevance = (mapResult, questionRelevance) => {
  const entries = Object.entries(questionRelevance ?? {})
  if (!entries.length) return mapResult

  // Read only: No need for the copy getOperations() makes.
  const pendingConditionQids = getQidsWithConditionOperationsToSend(
    mapResult.operationsBuffer.operations ??
      mapResult.operationsBuffer.getOperations()
  )

  return entries.reduce((result, [qid, relevance]) => {
    if (pendingConditionQids.includes(String(qid))) {
      skippedRelevance.set(String(qid), relevance)
      return result
    }

    skippedRelevance.delete(String(qid))
    return setQuestionRelevance(result, qid, relevance)
  }, mapResult)
}
