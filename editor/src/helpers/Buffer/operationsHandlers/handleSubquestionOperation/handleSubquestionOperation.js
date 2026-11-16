import { cloneDeep } from 'lodash'

export const handleSubquestionOperation = (
  _bufferOperations,
  _operation,
  _currentOperation,
  _newQuestionOperation
) => {
  if (!_operation) {
    return {
      bufferOperations: _bufferOperations,
      newOperation: {},
      addToBuffer: false,
    }
  }

  let newOperation
  let addToBuffer = false

  const bufferOperations = cloneDeep(_bufferOperations)
  const operation = cloneDeep(_operation)
  let currentOperation = cloneDeep(_currentOperation)
  const newQuestionOperation = cloneDeep(_newQuestionOperation)

  if (newQuestionOperation) {
    newQuestionOperation.props.subquestions = operation.props
    newOperation = newQuestionOperation
  } else {
    if (currentOperation) {
      currentOperation.props = operation.props
    } else {
      currentOperation = operation
      addToBuffer = true
    }

    newOperation = currentOperation
  }

  return { bufferOperations, newOperation, addToBuffer }
}
