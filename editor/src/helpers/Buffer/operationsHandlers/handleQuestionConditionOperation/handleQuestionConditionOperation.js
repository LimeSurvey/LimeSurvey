import { cloneDeep } from 'lodash'

// Imported directly, not from 'helpers': This module is loaded while
// 'helpers' is still loading (helpers -> Buffer -> operationsHandlers).
import { Entities } from '../../Entities'
import { Operations } from '../../Operations'
import { hasTempId } from '../../../hasTempId'
import { NEW_OBJECT_ID_PREFIX } from '../../../constants'
import {
  isConditionTempId,
  TEMP_ID_TOKEN_REGEX,
} from '../../../replaceTempIdsInObject'

// The condition actions of the API (see OpHandlerQuestionCondition.php).
export const CONDITION_ACTIONS = {
  CREATE: 'insertCondition',
  UPDATE: 'updateCondition',
  DELETE: 'deleteCondition',
  SCRIPT: 'conditionScript',
}

/**
 * A condition still waiting to be created must stay an insertCondition when it
 * is edited again before being sent: The edit arrives as an update of its temp
 * cid, which the server can't apply to a condition it doesn't have yet.
 */
const keepPendingCreate = (createCondition, editCondition) => ({
  ...editCondition,
  action: createCondition.action,
  tempId: createCondition.tempId,
  tempcids: createCondition.tempcids,
  cid: createCondition.cid,
})

// A condition create (insertCondition, carrying its temp ID).
const isPendingCreate = (condition) =>
  Boolean(condition?.tempId) || condition?.action === CONDITION_ACTIONS.CREATE

// Condition fields that can embed a question or subquestion temp ID (e.g. the
// cfieldname "Q12_Stemp__34" of an unsaved subquestion).
const SOURCE_FIELDS = ['cfieldname', 'cquestions', 'prevQuestionSGQA']

/**
 * The conditions of a list of scenarios (of a question or an operation).
 * @param {Array<{conditions: Array}>} [scenarios]
 * @returns {Array}
 */
export const getScenarioConditions = (scenarios) =>
  (scenarios ?? []).flatMap((scenario) => scenario.conditions ?? [])

const getOperationConditions = (op) =>
  getScenarioConditions(op?.props?.scenarios)

/**
 * Condition operations are keyed by condition: Each create (id = temp cid),
 * update (id = cid) or delete (id = cid) carries a single condition in a single
 * scenario, so the changes of one condition replace each other in the buffer.
 * A new condition is sent as an `update` operation with the insertCondition
 * action, not as a `create` operation: Every other operation waits until no
 * create operation is left in the buffer (see isOperationReadyForPatch), so a
 * new condition waiting for the ID of an unsaved source would hold back the
 * very operations saving that source (e.g. a subquestion update).
 * The expression script operation of a question uses the qid.
 */

// A condition create: An update operation carrying an insertCondition.
export const isConditionCreateOperation = (op) =>
  getOperationConditions(op).some(isPendingCreate)

/**
 * Whether the condition operation can't be sent yet:
 * - it failed (error), or its own ID is still a temp ID (except the create of
 *   the condition, keyed by its temp cid): An update or delete of a condition
 *   whose create is being sent waits for its real cid, the server can't find
 *   a condition by its temp ID;
 * - it references a question (qid/cqid) that still has a temp ID;
 * - a condition field embeds a temp ID: The server stores these fields as is.
 */
export const isConditionOperationWaiting = (op) => {
  if (op?.entity !== Entities.questionCondition) return false
  if (op.error) return true
  if (!isConditionCreateOperation(op) && hasTempId(op.id)) return true
  if (op.props && (hasTempId(op.props, 'qid') || hasTempId(op.props, 'cqid'))) {
    return true
  }

  return getOperationConditions(op).some((condition) =>
    SOURCE_FIELDS.some((key) => hasTempId(condition[key]))
  )
}

/**
 * The question of a condition operation.
 * @param {object} op buffer operation
 * @returns {number|string|undefined}
 */
export const getOperationQid = (op) => op?.props?.qid ?? op?.qid

/**
 * Whether `op` is a condition operation of the question `qid`: A condition
 * create, update or delete, or an expression script.
 * @param {object} op buffer operation
 * @param {number|string} qid
 * @returns {boolean}
 */
export const isConditionOperationOfQuestion = (op, qid) =>
  op?.entity === Entities.questionCondition &&
  String(getOperationQid(op)) === String(qid)

/**
 * Removes the condition operations of the given questions, e.g. those of a
 * deleted group: Sent after the group was deleted, the server would refuse
 * them.
 * Returns `bufferOperations` itself when there is nothing to remove.
 * @param {Array} bufferOperations
 * @param {Array<number|string>} qids
 * @returns {Array}
 */
export const removeConditionOperationsOfQuestions = (
  bufferOperations = [],
  qids = []
) => {
  const removedQids = new Set(qids.map(String))
  const kept = bufferOperations.filter(
    (op) =>
      op.entity !== Entities.questionCondition ||
      !removedQids.has(String(getOperationQid(op)))
  )

  return kept.length === bufferOperations.length ? bufferOperations : kept
}

/**
 * Whether `op` sets the expression script of a question.
 * @param {object} op buffer operation
 * @returns {boolean}
 */
export const isConditionScriptOperation = (op) =>
  op?.op === Operations.update && op?.props?.action === CONDITION_ACTIONS.SCRIPT

// An operation of the condition `cid`: Its create, update or delete.
const isOperationOfCondition = (op, cid) =>
  op?.entity === Entities.questionCondition &&
  !isConditionScriptOperation(op) &&
  String(op.id) === String(cid)

// The scenario and condition of a condition operation.
const getScenarioCondition = (op) => {
  const scenario = (op.props?.scenarios ?? [])[0]
  return { scid: scenario?.scid, condition: scenario?.conditions?.[0] }
}

// The operation with its single condition replaced, in the scenario `scid`.
const withCondition = (op, scid, condition) => ({
  ...op,
  props: { ...op.props, scenarios: [{ scid, conditions: [condition] }] },
})

/**
 * Whether the create of the condition is still waiting in the buffer, i.e.
 * the condition was never sent to the server. A buffered edit doesn't count:
 * The create of that condition may already be on its way to the server.
 */
const isConditionInBuffer = (bufferOperations, cid) =>
  bufferOperations.some(
    (op) => isOperationOfCondition(op, cid) && isConditionCreateOperation(op)
  )

const removeConditionFromBuffer = (bufferOperations, cid) =>
  bufferOperations.filter((op) => !isOperationOfCondition(op, cid))

/**
 * An expression script replaces every condition, so it drops all the buffered
 * condition operations of the question.
 */
const handleScriptOperation = (bufferOperations, operation, qid) => ({
  bufferOperations: bufferOperations.filter(
    (op) => !isConditionOperationOfQuestion(op, qid)
  ),
  newOperation: operation,
  addToBuffer: true,
})

/**
 * Deleting a condition replaces its buffered create and edits. Nothing is
 * deleted on the server when the condition only exists locally (its create is
 * still buffered): Such a delete operation could never be sent, and every
 * update would wait for it.
 * A temp condition whose create is no longer buffered is being sent: Its
 * delete waits in the buffer until the real cid replaces the temp one
 * (replaceTempIdsInSurveyAndBuffer), or is dropped when the create failed
 * (removeConditionOpsOfFailedCreates).
 */
const handleConditionDelete = (bufferOperations, operation) => {
  const cid = operation.id
  const isLocalOnly =
    hasTempId(cid) && isConditionInBuffer(bufferOperations, cid)

  return {
    bufferOperations: removeConditionFromBuffer(bufferOperations, cid),
    newOperation: isLocalOnly ? {} : operation,
    addToBuffer: !isLocalOnly,
  }
}

/**
 * Handles a condition operation in the buffer. Condition operations are keyed
 * by condition (see isConditionCreateOperation), so:
 * - a create, update or delete operation replaces the buffered operation of
 *   the same condition, in place;
 * - an edit of a condition whose create is still buffered is applied to that
 *   create, which keeps the insertCondition action;
 * - an expression script applies to every condition operation of the
 *   question.
 */
export const handleQuestionConditionOperation = (
  _bufferOperations,
  _operation
) => {
  if (!_operation) {
    return {
      bufferOperations: _bufferOperations,
      newOperation: {},
      addToBuffer: false,
    }
  }

  const bufferOperations = cloneDeep(_bufferOperations)
  const operation = cloneDeep(_operation)
  const qid = getOperationQid(operation)

  if (isConditionScriptOperation(operation)) {
    return handleScriptOperation(bufferOperations, operation, qid)
  }

  if (operation.op === Operations.delete) {
    return handleConditionDelete(bufferOperations, operation)
  }

  const index = bufferOperations.findIndex((op) =>
    isOperationOfCondition(op, operation.id)
  )
  if (index === -1) {
    return { bufferOperations, newOperation: operation, addToBuffer: true }
  }

  const buffered = bufferOperations[index]
  const { scid, condition } = getScenarioCondition(operation)
  bufferOperations[index] = isConditionCreateOperation(buffered)
    ? withCondition(
        buffered,
        scid,
        keepPendingCreate(getScenarioCondition(buffered).condition, condition)
      )
    : operation

  return { bufferOperations, newOperation: {}, addToBuffer: false }
}

// Question, subquestion or group temp IDs found in `value` (condition temp IDs,
// "temp__12_1", are skipped: They are not condition sources).
// Most values hold none: The regex only runs on those with the prefix.
const getEntityTempIds = (value) => {
  const string = String(value ?? '')
  if (!string.includes(NEW_OBJECT_ID_PREFIX)) return []

  return (string.match(TEMP_ID_TOKEN_REGEX) ?? []).filter(
    (token) => !isConditionTempId(token)
  )
}

/**
 * Whether the condition uses as source a question, subquestion or group temp ID
 * that isn't in `knownTempIds`, i.e. an unsaved entity that was deleted: That
 * temp ID will never be replaced.
 * @param {object} condition
 * @param {Set<string>} knownTempIds
 */
export const usesUnknownTempId = (condition, knownTempIds) =>
  ['cqid', ...SOURCE_FIELDS].some((key) =>
    getEntityTempIds(condition?.[key]).some((id) => !knownTempIds.has(id))
  )

/**
 * Remove the condition operations waiting for a temp ID that will never be
 * replaced (see usesUnknownTempId): Those of a question with an unknown temp
 * ID, and those of a condition using an unknown temp ID as source. Without
 * this, these operations would wait forever.
 * @param {Array} bufferOperations
 * @param {Set<string>} knownTempIds temp IDs of the entities still existing
 */
export const removeConditionOpsWithUnknownTempIds = (
  bufferOperations = [],
  knownTempIds
) =>
  bufferOperations.filter((op) => {
    if (op.entity !== Entities.questionCondition) return true

    const qid = getOperationQid(op)
    if (hasTempId(qid) && !knownTempIds.has(String(qid))) return false

    return !getOperationConditions(op).some((c) =>
      usesUnknownTempId(c, knownTempIds)
    )
  })

/**
 * The question, subquestion and group temp IDs the conditions use, in the
 * buffered condition operations and in the survey. A cheap check before
 * getKnownTempIds: Usually there are none.
 * @param {Array} bufferOperations
 * @param {object} survey
 * @returns {Set<string>}
 */
export const getConditionTempIds = (bufferOperations = [], survey) => {
  const ids = new Set()
  const add = (value) => getEntityTempIds(value).forEach((id) => ids.add(id))
  const addCondition = (condition) =>
    ['cqid', ...SOURCE_FIELDS].forEach((key) => add(condition?.[key]))

  bufferOperations
    .filter((op) => op.entity === Entities.questionCondition)
    .forEach((op) => {
      add(getOperationQid(op))
      getOperationConditions(op).forEach(addCondition)
    })
  ;(survey?.questionGroups ?? []).forEach((group) =>
    (group.questions ?? []).forEach((question) =>
      getScenarioConditions(question.scenarios).forEach(addCondition)
    )
  )

  return ids
}

/**
 * The temp IDs of the groups, questions, subquestions and answers still
 * existing: In the survey, or in the buffered (non condition) operations.
 * With `wantedIds` (e.g. getConditionTempIds), only these are looked for, and
 * the search stops once they are all found: The buffer, serialized operation
 * by operation, is then usually not read at all, the unsaved entities used by
 * the conditions being in the survey.
 * @param {object} survey
 * @param {Array} bufferOperations
 * @param {Set<string>} [wantedIds] the temp IDs to look for (default: all)
 * @returns {Set<string>}
 */
export const getKnownTempIds = (survey, bufferOperations = [], wantedIds) => {
  const ids = new Set()
  const isWanted = (id) => !wantedIds || wantedIds.has(id)
  const isDone = () => Boolean(wantedIds) && ids.size === wantedIds.size
  const add = (value) =>
    getEntityTempIds(value).forEach((id) => isWanted(id) && ids.add(id))

  if (wantedIds?.size === 0) return ids

  for (const group of survey?.questionGroups ?? []) {
    add(group.gid)
    for (const question of group.questions ?? []) {
      add(question.qid)
      ;(question.subquestions ?? []).forEach((sq) => add(sq.qid))
      ;(question.answers ?? []).forEach((answer) => add(answer.aid))
      if (isDone()) return ids
    }
  }

  for (const op of bufferOperations) {
    if (op.entity === Entities.questionCondition) continue
    add(JSON.stringify(op))
    if (isDone()) return ids
  }

  return ids
}

/**
 * Drops the operations of conditions still pointing at a temp cid whose create
 * is not in the buffer: Their deletes and edits. Such an operation waits for
 * the real cid of a condition whose create was being sent. Once no request is
 * running (after its ID mappings were applied, when it failed, or before a
 * save), a temp cid left without a buffered create belongs to a condition
 * creation that failed or was refused: The condition doesn't exist on the
 * server, so these operations could never be sent. A waiting delete operation
 * would even hold back every update, condition creates included.
 * Returns `bufferOperations` itself when there is nothing to drop.
 * @param {Array} bufferOperations
 * @returns {Array}
 */
export const removeConditionOpsOfFailedCreates = (bufferOperations = []) => {
  const conditionOps = bufferOperations.filter(
    (op) =>
      op.entity === Entities.questionCondition &&
      !isConditionScriptOperation(op)
  )
  const bufferedCreateCids = new Set(
    conditionOps.filter(isConditionCreateOperation).map((op) => String(op.id))
  )
  const orphanCids = new Set(
    conditionOps
      .filter(
        (op) =>
          hasTempId(op.id) &&
          !isConditionCreateOperation(op) &&
          !bufferedCreateCids.has(String(op.id))
      )
      .map((op) => String(op.id))
  )
  if (!orphanCids.size) return bufferOperations

  return bufferOperations.filter(
    (op) =>
      !(orphanCids.has(String(op.id)) && isOperationOfCondition(op, op.id))
  )
}

/**
 * Whether a condition create that was sent got no real cid back: The request
 * failed, or the server skipped the condition (e.g. it already exists). The
 * editor still shows that condition, although the server doesn't have it.
 * @param {Array} sentOperations the operations of the request
 * @param {Array<{tempId: string, id: number}>} [mappings] the condition ID
 *   mappings of the response
 * @returns {boolean}
 */
export const hasUnansweredConditionCreate = (sentOperations = [], mappings) => {
  const answered = new Set((mappings ?? []).map(({ tempId }) => String(tempId)))

  return sentOperations.some(
    (op) =>
      op.entity === Entities.questionCondition &&
      isConditionCreateOperation(op) &&
      !answered.has(String(op.id))
  )
}
