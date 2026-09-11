import { cloneDeep, merge } from 'lodash'

import { Entities, Operations } from 'helpers'

export const handleQuestionConditionOperation = (
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

  if (operation.op === Operations.delete) {
    let updatedBufferOperations = bufferOperations.filter((op) => {
      return !(
        op.id === operation.id && op.entity === Entities.questionCondition
      )
    })

    return {
      bufferOperations: updatedBufferOperations,
      newOperation: operation,
      addToBuffer: true,
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
