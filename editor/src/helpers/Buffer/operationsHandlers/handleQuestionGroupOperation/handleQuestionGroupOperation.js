import { cloneDeep, merge } from 'lodash'

import { hasTempId, Operations } from 'helpers'

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
    let updatedBufferOperations = bufferOperations.filter((op) => {
      if (operation.props?.question && operation.props.question.gid === op.id) {
        return false
      } else if (operation.id === op.id) {
        return false
      }

      return true
    })

    return {
      bufferOperations: updatedBufferOperations,
      newOperation: isAddingNewQuestionGroup ? {} : operation,
      addToBuffer: isAddingNewQuestionGroup ? false : true,
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
