import { cloneDeep, merge, uniqWith, isEqual } from 'lodash'

import {
  Entities,
  Operations,
  isTempId,
  removeScenarioConditions,
} from 'helpers'
import { CONDITION_ACTIONS, FIELDNAME_TEMP_ID_REGEX } from 'helpers/constants'

const INSERT_CONDITION = CONDITION_ACTIONS.CONDITION.CREATE
const UPDATE_CONDITION = CONDITION_ACTIONS.CONDITION.UPDATE
const DELETE_SCENARIO = CONDITION_ACTIONS.SCENARIO.DELETE
const CONDITION_SCRIPT = CONDITION_ACTIONS.CONDITION_SCRIPT.UPDATE

const isConditionOperation = (operation) =>
  operation?.entity === Entities.questionCondition &&
  Array.isArray(operation.props?.scenarios)

/**
 * Lists the conditions of a condition operation, over all its scenarios.
 *
 * @param {object} operation A buffer operation.
 * @returns {Array} The conditions, empty for other operations.
 */
const conditionsOf = (operation) =>
  isConditionOperation(operation)
    ? operation.props.scenarios.flatMap((scenario) => scenario.conditions ?? [])
    : []

/**
 * Lists the temp question and subquestion IDs a condition references: As
 * qid or cqid, or inside a fieldname. User values (constants, regular
 * expressions) are not IDs, only a value that references another question.
 *
 * @param {object} condition A condition of a condition operation.
 * @returns {string[]} The referenced temp IDs.
 */
const getReferencedTempIds = (condition) => {
  const ids = [condition.qid, condition.cqid]
    .filter((id) => isTempId(id))
    .map(String)

  const fieldnames = [
    condition.cfieldname,
    condition.cquestions,
    condition.prevQuestionSGQA,
    /^@.*@$/.test(condition.value) ? condition.value : null,
  ].filter((fieldname) => typeof fieldname === 'string')

  fieldnames.forEach((fieldname) => {
    for (const [, , id] of fieldname.matchAll(FIELDNAME_TEMP_ID_REGEX)) {
      ids.push(id)
    }
  })

  return ids
}

/**
 * Checks whether a condition operation still waits for a real ID: Of the
 * question it belongs to, of a question or subquestion it references, or of
 * a condition that is updated or deleted while its insert is being saved.
 * An insert is sent with its own temp ID on purpose: The server maps it.
 *
 * @param {object} operation A buffer operation.
 * @returns {boolean} Whether the operation has to wait.
 */
export const isConditionWaitingForTempId = (operation) =>
  operation.entity === Entities.questionCondition &&
  (isTempId(operation.props?.qid) ||
    conditionsOf(operation).some(
      (condition) =>
        getReferencedTempIds(condition).length > 0 ||
        (condition.action !== INSERT_CONDITION && isTempId(condition.cid))
    ))

/**
 * Removes the conditions matching the filter from a condition operation,
 * and the scenarios left without conditions.
 */
const removeConditions = (operation, shouldRemove) => {
  if (!isConditionOperation(operation)) return operation

  const scenarios = removeScenarioConditions(
    operation.props.scenarios,
    shouldRemove
  )
  return scenarios === operation.props.scenarios
    ? operation
    : { ...operation, props: { ...operation.props, scenarios } }
}

const hasScenarios = (operation) =>
  !isConditionOperation(operation) || operation.props.scenarios.length > 0

/**
 * Finds the conditions with a temp ID that wait in the buffer: Inserts that
 * aren't sent yet, and edits whose insert is being saved or was sent back by
 * a failed save.
 *
 * @param {Array} bufferOperations The buffered operations.
 * @returns {{inserts: object, edits: object}} The conditions by temp cid.
 */
const getPendingConditions = (bufferOperations) => {
  const inserts = {}
  const edits = {}

  bufferOperations
    .filter((operation) => operation.op === Operations.update)
    .flatMap(conditionsOf)
    .filter((condition) => isTempId(condition.cid))
    .forEach((condition) => {
      if (condition.action === INSERT_CONDITION)
        inserts[condition.cid] = condition
      if (condition.action === UPDATE_CONDITION)
        edits[condition.cid] = condition
    })

  return { inserts, edits }
}

/**
 * Puts each condition into the scenario it belongs to: The server reads the
 * scenario from the container, not from the condition.
 *
 * @param {Array} scenarios Scenarios of a condition operation.
 * @returns {Array} The scenarios, regrouped.
 */
const regroupByScenario = (scenarios) =>
  scenarios.reduce((result, scenario) => {
    if (!Array.isArray(scenario.conditions)) {
      result.push(scenario)
      return result
    }

    scenario.conditions.forEach((condition) => {
      const scid = condition.scenario ?? scenario.scid
      let target = result.find(
        (item) => item.scid == scid && Array.isArray(item.conditions)
      )
      if (!target) {
        target = { ...scenario, scid, conditions: [] }
        result.push(target)
      }
      target.conditions.push(condition)
    })

    return result
  }, [])

/**
 * Merges the scenarios of a new condition operation into the current one:
 * Scenarios are matched by scid and conditions by cid, a condition that
 * appears again (edited or moved to another scenario) replaces the old one.
 */
const mergeScenarios = (currentScenarios, newScenarios) => {
  const newCids = newScenarios.flatMap(
    (scenario) => scenario.conditions?.map((condition) => condition.cid) ?? []
  )

  const scenarios = [
    ...removeScenarioConditions(currentScenarios, (condition) =>
      newCids.includes(condition.cid)
    ),
  ]

  newScenarios.forEach((newScenario) => {
    const index = scenarios.findIndex(
      (item) => item.scid == newScenario.scid && Array.isArray(item.conditions)
    )

    if (index !== -1 && Array.isArray(newScenario.conditions)) {
      scenarios[index] = {
        ...scenarios[index],
        conditions: [...scenarios[index].conditions, ...newScenario.conditions],
      }
    } else {
      scenarios.push(newScenario)
    }
  })

  return scenarios
}

/**
 * Handles a condition delete: Conditions and scenarios that are still waiting
 * in the buffer to be inserted are removed from it and not sent at all, like
 * questions with a temp ID. Deletes of the same question are combined, so
 * deleting two scenarios before a save deletes both.
 */
const handleDeleteOperation = (bufferOperations, operation) => {
  const { inserts: pendingInserts } = getPendingConditions(bufferOperations)
  const deletedScenarios = operation.props?.scenarios ?? []

  const deletedScids = deletedScenarios
    .filter((scenario) => scenario.action === DELETE_SCENARIO)
    .map((scenario) => scenario.scid)
  const deletedCids = conditionsOf(operation).map((condition) => condition.cid)
  const deletedPendingCids = deletedCids.filter((cid) => pendingInserts[cid])

  const pendingDelete = bufferOperations.find(
    (bufferOperation) =>
      bufferOperation.op === Operations.delete &&
      bufferOperation.entity === Entities.questionCondition &&
      bufferOperation.id === operation.id &&
      bufferOperation.qid == operation.qid
  )

  // Buffered inserts and updates of deleted conditions and scenarios are
  // dropped: They would be sent after the delete.
  const updatedBufferOperations = bufferOperations
    .filter((bufferOperation) => bufferOperation !== pendingDelete)
    .map((bufferOperation) => {
      if (
        bufferOperation.op === Operations.delete ||
        !isConditionOperation(bufferOperation)
      ) {
        return bufferOperation
      }

      const isSameQuestion = bufferOperation.qid == operation.qid

      // A delete sent again after a failed request is older than the pending
      // saves: They may belong to a new scenario with the same number.
      return removeConditions(
        bufferOperation,
        (condition, scenario) =>
          deletedCids.includes(condition.cid) ||
          (!operation.error &&
            isSameQuestion &&
            deletedScids.includes(scenario.scid))
      )
    })
    .filter(hasScenarios)

  const remainingOperation = removeConditions(operation, (condition) =>
    deletedPendingCids.includes(condition.cid)
  )

  // A question with a temp ID has no saved scenarios to delete.
  const isNothingToDelete =
    !remainingOperation.props?.scenarios?.length || isTempId(operation.qid)

  if (isNothingToDelete) {
    return {
      bufferOperations: pendingDelete
        ? [...updatedBufferOperations, pendingDelete]
        : updatedBufferOperations,
      newOperation: {},
      addToBuffer: false,
    }
  }

  return {
    bufferOperations: updatedBufferOperations,
    newOperation: pendingDelete
      ? {
          ...pendingDelete,
          props: {
            ...pendingDelete.props,
            scenarios: uniqWith(
              [
                ...pendingDelete.props.scenarios,
                ...remainingOperation.props.scenarios,
              ],
              isEqual
            ),
          },
        }
      : remainingOperation,
    addToBuffer: true,
  }
}

const isConditionScriptOperation = (operation) =>
  operation?.entity === Entities.questionCondition &&
  operation.props?.action === CONDITION_SCRIPT

// The pending inserts/updates and ExpressionScript of a question.
const isSaveOperationOfQuestion = (operation, qid) =>
  operation.entity === Entities.questionCondition &&
  operation.op === Operations.update &&
  (operation.qid ?? operation.props?.qid) == qid

/**
 * Handles an ExpressionScript: It replaces all conditions of the question,
 * so it replaces the pending condition saves and scripts of the question.
 * A script sent back by a failed save is older than them: It is put back
 * before them, or dropped if a newer script is pending.
 */
const handleConditionScriptOperation = (
  bufferOperations,
  operation,
  restore
) => {
  const qid = operation.qid ?? operation.props?.qid

  if (!operation.error) {
    return {
      bufferOperations: bufferOperations.filter(
        (bufferOperation) => !isSaveOperationOfQuestion(bufferOperation, qid)
      ),
      newOperation: operation,
      addToBuffer: true,
    }
  }

  const hasNewerScript = bufferOperations.some(
    (bufferOperation) =>
      isConditionScriptOperation(bufferOperation) &&
      isSaveOperationOfQuestion(bufferOperation, qid)
  )
  if (hasNewerScript) {
    return { bufferOperations, newOperation: {}, addToBuffer: false }
  }

  // Only a refused operation keeps its error, a failed request is retried.
  const script = restore ? operation : { ...operation, error: false }
  const index = bufferOperations.findIndex((bufferOperation) =>
    isSaveOperationOfQuestion(bufferOperation, qid)
  )
  const updatedBufferOperations = [...bufferOperations]
  updatedBufferOperations.splice(
    index === -1 ? updatedBufferOperations.length : index,
    0,
    script
  )

  return {
    bufferOperations: updatedBufferOperations,
    newOperation: {},
    addToBuffer: false,
  }
}

/**
 * Handles saving (inserting and updating) conditions. The server applies only
 * the script of an operation that has one, so the conditions are merged into
 * the pending condition save of the question, never into a pending script:
 * They go into their own operation after it.
 */
const handleSaveConditionsOperation = (bufferOperations, operation) => {
  const { inserts: pendingInserts, edits: pendingEdits } =
    getPendingConditions(bufferOperations)

  operation.props.scenarios = regroupByScenario(
    operation.props.scenarios.map((scenario) => ({
      ...scenario,
      conditions: scenario.conditions?.map((condition) => {
        // Editing a condition that is still waiting to be inserted:
        // Send the edited condition as the insert, like questions.
        const pendingInsert = pendingInserts[condition.cid]
        if (condition.action === UPDATE_CONDITION && pendingInsert) {
          return {
            ...condition,
            action: INSERT_CONDITION,
            tempId: pendingInsert.tempId,
            tempcids: pendingInsert.tempcids,
          }
        }

        // An insert coming back from a failed save is older than a waiting
        // edit of the condition: Send the edit's newest values as the insert.
        const pendingEdit = pendingEdits[condition.cid]
        if (condition.action === INSERT_CONDITION && pendingEdit) {
          return {
            ...pendingEdit,
            action: INSERT_CONDITION,
            tempId: condition.tempId,
            tempcids: condition.tempcids,
          }
        }

        return condition
      }),
    }))
  )

  const pendingSave = bufferOperations.find(
    (bufferOperation) =>
      isSaveOperationOfQuestion(
        bufferOperation,
        operation.qid ?? operation.props?.qid
      ) && isConditionOperation(bufferOperation)
  )
  const savedCids = conditionsOf(operation).map((condition) => condition.cid)

  // The pending save is replaced by the merged one, added at the end of the
  // buffer: After a pending script of the question.
  const otherOperations = bufferOperations
    .filter((bufferOperation) => bufferOperation !== pendingSave)
    .map((bufferOperation) =>
      bufferOperation.op === Operations.update
        ? removeConditions(bufferOperation, (condition) =>
            savedCids.includes(condition.cid)
          )
        : bufferOperation
    )
    .filter(hasScenarios)

  const newOperation = pendingSave
    ? {
        ...pendingSave,
        props: {
          ...merge({}, pendingSave.props, {
            ...operation.props,
            scenarios: undefined,
          }),
          scenarios: mergeScenarios(
            pendingSave.props.scenarios,
            operation.props.scenarios
          ),
        },
      }
    : operation

  return {
    bufferOperations: otherOperations,
    newOperation,
    addToBuffer: true,
  }
}

/**
 * Lists the temp IDs of the conditions inserted by the given operations.
 *
 * @param {Array} operations Buffer operations, e.g. the ones sent in a save.
 * @returns {string[]} The temp condition IDs of the inserts.
 */
export const getInsertedTempConditionIds = (operations) =>
  Object.keys(getPendingConditions(operations).inserts)

/**
 * Checks whether a condition references a question or subquestion with a
 * temp ID that is gone: It was deleted before it was saved.
 *
 * @param {object} condition A buffered or saved condition.
 * @param {Set<string>} existingTempIds Temp IDs of the questions and subquestions in the survey.
 * @returns {boolean} Whether the condition can never be saved.
 */
export const referencesDeletedTempId = (condition, existingTempIds) =>
  getReferencedTempIds(condition).some((id) => !existingTempIds.has(id))

/**
 * Checks whether a condition save that is sent again after a failed request
 * was replaced in the meantime: The user saved an ExpressionScript for the
 * question, which replaces its conditions, and that script wasn't part of
 * the failed request.
 *
 * @param {object} operation The operation that is sent again.
 * @param {Array} bufferOperations The buffered operations.
 * @param {Array} sentOperations The operations of the failed request.
 * @returns {boolean} Whether the operation is obsolete.
 */
export const isReplacedByNewerScript = (
  operation,
  bufferOperations,
  sentOperations
) => {
  if (operation.op !== Operations.update || !isConditionOperation(operation)) {
    return false
  }

  const qid = operation.qid ?? operation.props?.qid
  const isScriptOfQuestion = (bufferOperation) =>
    isConditionScriptOperation(bufferOperation) &&
    isSaveOperationOfQuestion(bufferOperation, qid)

  return (
    bufferOperations.some(isScriptOfQuestion) &&
    !sentOperations.some(isScriptOfQuestion)
  )
}

/**
 * Removes the buffered conditions that reference a question or subquestion
 * with a temp ID that is gone: It was deleted before it was saved, so it
 * never gets a real ID and the condition would wait for it forever.
 *
 * @param {Array} bufferOperations The buffered operations.
 * @param {Set<string>} existingTempIds Temp IDs of the questions and subquestions in the survey.
 * @returns {Array} The operations, the same array if nothing was removed.
 */
export const removeConditionsWithDeletedTempIds = (
  bufferOperations,
  existingTempIds
) => {
  const isDeleted = (id) => !existingTempIds.has(String(id))
  const isAffected = (operation) =>
    operation.entity === Entities.questionCondition &&
    ((isTempId(operation.props?.qid) && isDeleted(operation.props.qid)) ||
      conditionsOf(operation).some((condition) =>
        referencesDeletedTempId(condition, existingTempIds)
      ))

  if (!bufferOperations.some(isAffected)) return bufferOperations

  return bufferOperations
    .filter(
      (operation) =>
        !(
          operation.entity === Entities.questionCondition &&
          isTempId(operation.props?.qid) &&
          isDeleted(operation.props.qid)
        )
    )
    .map((operation) =>
      removeConditions(operation, (condition) =>
        referencesDeletedTempId(condition, existingTempIds)
      )
    )
    .filter(hasScenarios)
}

/**
 * Settles temp condition IDs that won't get a real ID from a running save:
 * Conditions whose insert is back in the buffer (the save failed), and
 * inserts the server rejected. Like before the insert is sent, a delete
 * cancels the insert, and an edit is sent as the insert.
 *
 * @param {Array} bufferOperations The buffered operations.
 * @param {string[]} rejectedCids Temp IDs of inserts the server rejected.
 * @returns {Array} The buffered operations, settled.
 */
export const settleTempConditionIds = (bufferOperations, rejectedCids = []) => {
  const tempCids = [
    ...getInsertedTempConditionIds(bufferOperations),
    ...rejectedCids,
  ]
  if (!tempCids.length) return bufferOperations

  const conditionOperations = bufferOperations.filter(isConditionOperation)

  const deletedCids = tempCids.filter((cid) =>
    conditionOperations.some(
      (op) =>
        op.op === Operations.delete &&
        conditionsOf(op).some((condition) => condition.cid === cid)
    )
  )
  const editedCids = tempCids.filter(
    (cid) =>
      !deletedCids.includes(cid) &&
      conditionOperations.some((op) =>
        conditionsOf(op).some(
          (condition) =>
            condition.cid === cid && condition.action === UPDATE_CONDITION
        )
      )
  )

  if (!deletedCids.length && !editedCids.length) return bufferOperations

  return bufferOperations
    .map((op) => {
      if (!isConditionOperation(op)) return op

      // Deleted: Drop the delete and the insert. Edited: Drop the old insert.
      const settled = removeConditions(
        op,
        (condition) =>
          deletedCids.includes(condition.cid) ||
          (editedCids.includes(condition.cid) &&
            condition.action === INSERT_CONDITION)
      )

      // Edited: The edit, with the newest values, becomes the insert.
      const scenarios = settled.props.scenarios.map((scenario) =>
        Array.isArray(scenario.conditions)
          ? {
              ...scenario,
              conditions: scenario.conditions.map((condition) =>
                editedCids.includes(condition.cid) &&
                condition.action === UPDATE_CONDITION
                  ? {
                      ...condition,
                      action: INSERT_CONDITION,
                      tempId: condition.cid,
                      tempcids: [condition.cid],
                    }
                  : condition
              ),
            }
          : scenario
      )

      return { ...settled, props: { ...settled.props, scenarios } }
    })
    .filter(hasScenarios)
}

/**
 * Handles a condition operation that is added to the buffer.
 *
 * @param {Array} _bufferOperations The buffered operations.
 * @param {object} _operation The added operation.
 * @param {object} _currentOperation The buffered operation with the same key.
 * @param {boolean} restore Whether a refused operation is put back.
 * @returns {{bufferOperations: Array, newOperation: object, addToBuffer: boolean}} The new buffer state.
 */
export const handleQuestionConditionOperation = (
  _bufferOperations,
  _operation,
  _currentOperation,
  restore = false
) => {
  if (!_operation) {
    return {
      bufferOperations: _bufferOperations,
      newOperation: {},
      addToBuffer: false,
    }
  }

  let bufferOperations = cloneDeep(_bufferOperations)
  let currentOperation = cloneDeep(_currentOperation)
  const operation = cloneDeep(_operation) || {}

  if (operation.op === Operations.delete) {
    return handleDeleteOperation(bufferOperations, operation)
  }

  if (isConditionScriptOperation(operation)) {
    return handleConditionScriptOperation(bufferOperations, operation, restore)
  }

  if (isConditionOperation(operation)) {
    return handleSaveConditionsOperation(bufferOperations, operation)
  }

  // Handle create or update operation
  const newOperation = currentOperation
    ? {
        ...currentOperation,
        props: merge({}, currentOperation.props, operation.props),
      }
    : operation

  return {
    bufferOperations,
    newOperation,
    addToBuffer: !currentOperation,
  }
}
