import { cloneDeep, merge } from 'lodash'

import { Entities, hasTempId, Operations } from 'helpers'

/**
 * Temp IDs of the questions of the group whose create is still in the buffer,
 * i.e. never sent to the server (e.g. added while a save is running, or
 * waiting for the real ID of a new group).
 * When the group is deleted, all the operations of these questions must go
 * with it. Left in the buffer, the question create would block every save of
 * the survey until a reload:
 * - its group was saved: The group delete is queued next to the create, and
 *   the buffer sends nothing while it holds both a create and a delete
 *   (creates and deletes each wait for the other, see isOperationReadyForPatch);
 * - its group was new: The create waits for a group ID that never comes, and
 *   meanwhile every other operation waits for the creates to be sent.
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
        hasTempId(String(op.id)) &&
        String(op.props?.question?.gid) === String(gid)
    )
    .map((op) => String(op.id))

const getQuestionEntities = () => [
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
    // dropConditionsWithUnknownTempIds (SurveyLogicProvider).
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
        getQuestionEntities().includes(op.entity) &&
        unsavedQids.includes(String(op.id))
      ) {
        return false
      }

      return true
    })

    return {
      bufferOperations: updatedBufferOperations,
      newOperation: isAddingNewQuestionGroup ? {} : operation,
      addToBuffer: !isAddingNewQuestionGroup,
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
