// Imported directly, not from 'helpers': This module is loaded while
// 'helpers' is still loading (helpers -> Buffer -> operationsHandlers).
import { Entities } from '../Entities'
import { Operations } from '../Operations'
import { hasTempId } from '../../hasTempId'
import { removeConditionOpsOfFailedCreates } from './handleQuestionConditionOperation/handleQuestionConditionOperation'

// Entities whose delete is kept while their create is being sent.
const ENTITIES = [Entities.question, Entities.questionGroup]

/**
 * Whether the create of the entity is still waiting in the buffer, i.e. it
 * was never sent to the server.
 * @param {Array} bufferOperations
 * @param {string} entity e.g. Entities.question
 * @param {number|string} id the temp ID of the entity
 * @returns {boolean}
 */
export const isCreateBuffered = (bufferOperations, entity, id) =>
  bufferOperations.some(
    (op) =>
      op.entity === entity &&
      op.op === Operations.create &&
      String(op.id) === String(id)
  )

/**
 * Drops the deletes of new questions and groups whose create is no longer
 * in the buffer and still has a temp ID. Such a delete was queued while the
 * create was being sent, to wait for the real ID. Once no request is running
 * (after its ID mappings were applied, or before a save), a temp ID left on
 * it means the create failed: The entity doesn't exist on the server, and the
 * delete could never be sent. Waiting, it would hold back every update.
 * Returns `bufferOperations` itself when there is nothing to drop.
 * @param {Array} bufferOperations
 * @returns {Array}
 */
export const removeDeletesOfFailedCreates = (bufferOperations = []) => {
  const key = (op) => `${op.entity}:${op.id}`
  const bufferedCreates = new Set(
    bufferOperations.filter((op) => op.op === Operations.create).map(key)
  )
  const kept = bufferOperations.filter(
    (op) =>
      !(
        ENTITIES.includes(op.entity) &&
        op.op === Operations.delete &&
        hasTempId(op.id) &&
        !bufferedCreates.has(key(op))
      )
  )

  return kept.length === bufferOperations.length ? bufferOperations : kept
}

/**
 * Drops every operation that waits for the real ID of a creation that failed
 * or was refused: Condition changes (removeConditionOpsOfFailedCreates) and
 * deletes of new questions and groups (removeDeletesOfFailedCreates). Call it
 * only when no request is running.
 * Returns `bufferOperations` itself when there is nothing to drop.
 * @param {Array} bufferOperations
 * @returns {Array}
 */
export const removeOperationsOfFailedCreates = (bufferOperations = []) =>
  removeDeletesOfFailedCreates(
    removeConditionOpsOfFailedCreates(bufferOperations)
  )
