import { cloneDeep, merge } from 'lodash'

import { hasTempId, Operations } from 'helpers'

// Imported directly: 'helpers' is still loading when this module is evaluated.
import { Entities } from '../../Entities'
import { isCreateBuffered } from '../removeDeletesOfFailedCreates'

/**
 * Temp IDs of the questions of the group whose create is still in the buffer,
 * i.e. never sent to the server (e.g. added while a save is running, or
 * waiting for the real ID of a new group).
 * When the group is deleted, all the operations of these questions must go
 * with it. Left in the buffer:
 * - its group was saved: The question create would be sent after the group
 *   delete and refused by the server;
 * - its group was new: The create would wait for a group ID that never comes,
 *   and meanwhile every update waits for the creates to be sent (see
 *   isOperationReadyForPatch), until a reload.
 * @param {Array} bufferOperations
 * @param {string|number} gid the deleted group
 * @returns {Array<string>}
 */
const getUnsavedQuestionIdsOfGroup = (bufferOperations, gid) =>
  bufferOperations
    .filter(
      (op) =>
        op.entity === Entities.question &&
        op.op === Operations.create &&
        hasTempId(op.id) &&
        String(op.props?.question?.gid) === String(gid)
    )
    .map((op) => String(op.id))

const QUESTION_ENTITIES = [
  Entities.question,
  Entities.questionL10n,
  Entities.questionAttribute,
  Entities.answer,
  Entities.subquestion,
]

export const handleQuestionGroupOperation = (
  _bufferOperations = [],
  _operation,
  _currentOperation = null
) => {
  if (!_operation) {
    return {
      bufferOperations: _bufferOperations,
      newOperation: {},
      addToBuffer: false,
    }
  }

  const bufferOperations = cloneDeep(_bufferOperations)
  let currentOperation = cloneDeep(_currentOperation)
  const operation = cloneDeep(_operation) || {}

  const isAddingNewQuestionGroup = hasTempId(operation.id)

  // Handle delete operation
  if (operation.op === Operations.delete) {
    // The unsaved questions of the group go with it: Their operations could
    // never be sent since their temp IDs will never be replaced. The condition
    // operations of them or using them as source are dropped by
    // cleanConditionOperations (SurveyLogicProvider).
    // A new group exists only locally while its create is still buffered:
    // Nothing to delete on the server. Once the create was sent, the delete
    // is kept: It waits for the real ID, otherwise the group would be
    // created on the server and stay there.
    const isLocalOnly =
      isAddingNewQuestionGroup &&
      isCreateBuffered(bufferOperations, Entities.questionGroup, operation.id)

    const unsavedQids = getUnsavedQuestionIdsOfGroup(
      bufferOperations,
      operation.id
    )

    const updatedBufferOperations = bufferOperations.filter((op) => {
      if (operation.props?.question && operation.props.question.gid === op.id) {
        return false
      } else if (operation.id === op.id) {
        return false
      } else if (
        QUESTION_ENTITIES.includes(op.entity) &&
        unsavedQids.includes(String(op.id))
      ) {
        return false
      }

      return true
    })

    return {
      bufferOperations: updatedBufferOperations,
      newOperation: isLocalOnly ? {} : operation,
      addToBuffer: !isLocalOnly,
    }
  }

  if (!_currentOperation) {
    return {
      bufferOperations,
      newOperation: operation,
      addToBuffer: true,
    }
  }

  const newOperation = {
    ...currentOperation,
    props: merge({}, currentOperation.props, operation.props),
  }

  return { bufferOperations, newOperation, addToBuffer: false }
}
