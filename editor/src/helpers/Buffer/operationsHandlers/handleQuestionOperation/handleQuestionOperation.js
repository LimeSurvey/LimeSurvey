import { cloneDeep, merge } from 'lodash'

import { Entities, Operations, hasTempId } from 'helpers'

import { isConditionOperationOfQuestion } from '../handleQuestionConditionOperation/handleQuestionConditionOperation'
import { isCreateBuffered } from '../removeDeletesOfFailedCreates'

export const handleQuestionOperation = (
  _bufferOperations,
  _operation,
  _currentOperation
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

  const questionHasATempId = hasTempId(operation.id)

  if (operation.op === Operations.delete) {
    // A new question exists only locally while its create is still buffered:
    // Nothing to delete on the server. Once the create was sent, the delete
    // is kept: It waits for the real ID, otherwise the question would be
    // created on the server and stay there.
    const isLocalOnly =
      questionHasATempId &&
      isCreateBuffered(bufferOperations, Entities.question, operation.id)

    // Remove all the operations that depend on this question. Condition
    // operations are keyed by condition, not by question: Those of the
    // question go by their qid, and one of another question is kept even when
    // its cid equals this qid.
    let updatedBufferOperations = bufferOperations.filter((op) => {
      if (op.entity === Entities.questionCondition) {
        return !isConditionOperationOfQuestion(op, operation.id)
      }

      return op.id !== operation.id
    })

    return {
      bufferOperations: updatedBufferOperations,
      newOperation: isLocalOnly ? {} : operation,
      addToBuffer: !isLocalOnly,
    }
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
