import { cloneDeep, merge } from 'lodash'

import { Operations, hasTempId } from 'helpers'

import { removeConditionOperationsOfQuestion } from '../handleQuestionConditionOperation/handleQuestionConditionOperation'

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
    // remove all the operations that depends on this question.
    let updatedBufferOperations = bufferOperations.filter((op) => {
      if (op.id === operation.id) {
        return false
      }

      return true
    })

    // A question that was never saved will never get its real ID, so the
    // condition operations of it, or using it as source, could never be sent.
    if (questionHasATempId) {
      updatedBufferOperations = removeConditionOperationsOfQuestion(
        updatedBufferOperations,
        operation.id
      )
    }

    return {
      bufferOperations: updatedBufferOperations,
      newOperation: questionHasATempId ? {} : operation,
      addToBuffer: !questionHasATempId,
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
