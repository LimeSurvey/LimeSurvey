import { cloneDeep, merge } from 'lodash'

export const handleSurveyOperation = (
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

  const bufferOperations = cloneDeep(_bufferOperations)
  const operation = cloneDeep(_operation)
  let currentOperation = cloneDeep(_currentOperation)

  if (currentOperation) {
    const newOperation = {
      ...currentOperation,
      props: merge({}, currentOperation.props, operation.props),
    }

    /**
     * AdditionalLanguages requires a special treatment, because it has an array value
     * So we don't want to merge the two arrays but instead we want to override the array with the new array.
     */
    if (operation.props.additionalLanguages) {
      newOperation.props.additionalLanguages =
        operation.props.additionalLanguages
    }

    return { bufferOperations, newOperation, addToBuffer: false }
  }

  return { bufferOperations, newOperation: operation, addToBuffer: true }
}
