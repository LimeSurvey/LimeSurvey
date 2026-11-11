import { useCallback } from 'react'
import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { OperationsBuffer, STATES } from 'helpers'

import { removeConditionOperationsOfQuestions } from 'helpers/Buffer/operationsHandlers'

import { useErrors } from './useErrors'

/**
 * The buffered operations as stored, without the deep copy OperationsBuffer
 * makes: For components that only read them. Re-renders on every buffer change.
 * @returns {Array} the buffered operations (don't mutate them)
 */
export const useBufferOperations = () => {
  const { data } = useQuery({
    queryKey: [STATES.BUFFER],
    queryFn: () => [],
    cacheTime: Infinity,
    staleTime: Infinity,
  })

  return data
}

export const useBuffer = () => {
  const data = useBufferOperations()

  let { data: hash = '' } = useQuery({
    queryKey: [STATES.BUFFER_HASH],
    queryFn: () => '',
    cacheTime: Infinity,
    staleTime: Infinity,
  })

  const { removeError } = useErrors()

  const setBuffer = (data, hash) => {
    queryClient.setQueryData([STATES.BUFFER], data)
    if (hash) {
      queryClient.setQueryData([STATES.BUFFER_HASH], hash)
    }
  }

  const clearBuffer = useCallback(({ ready } = {}) => {
    const operationBuffer = new OperationsBuffer(
      queryClient.getQueryData([STATES.BUFFER])
    )
    const newOperations =
      ready === undefined
        ? []
        : operationBuffer.getOperations({
            ready: !ready,
          })
    queryClient.setQueryData([STATES.BUFFER], newOperations)
  }, [])

  /**
   * Adds several operations with a single buffer write (one copy of the
   * buffer, one save trigger), e.g. the conditions of a scenario.
   * @param {Array} operations
   * @returns {void}
   */
  const addOperationsToBuffer = (operations = []) => {
    if (!operations.length) return
    if (!queryClient.getQueryData([STATES.SURVEY])?.survey) return
    if (process.env.STORYBOOK_DEV === 'true') return

    const operationBuffer = new OperationsBuffer(
      queryClient.getQueryData([STATES.BUFFER])
    )
    operations.forEach((operation) => operationBuffer.addOperation(operation))
    operationBuffer.setBufferHash(Math.random())

    setBuffer(operationBuffer.getOperations(), operationBuffer.bufferHash)
    operations.forEach((operation) =>
      removeError(operation.id, operation.entity)
    )
  }

  const addToBuffer = (operation, updateCurrentOperation = true) => {
    const operationBuffer = new OperationsBuffer(
      queryClient.getQueryData([STATES.BUFFER])
    )
    const survey = queryClient.getQueryData([STATES.SURVEY])?.survey

    if (!survey) {
      return
    }

    if (process.env.STORYBOOK_DEV === 'true') {
      return
    }

    operationBuffer.addOperation(operation, updateCurrentOperation)
    operationBuffer.setBufferHash(Math.random())

    setBuffer(operationBuffer.getOperations(), operationBuffer.bufferHash)
    removeError(operation.id, operation.entity)
  }

  /**
   * Removes the buffered condition operations of the given questions, e.g.
   * before their group is deleted.
   * @param {Array<number|string>} qids
   * @returns {void}
   */
  const removeConditionOperationsOfQuestionsFromBuffer = (qids = []) => {
    const operations = queryClient.getQueryData([STATES.BUFFER]) ?? []
    const kept = removeConditionOperationsOfQuestions(operations, qids)

    if (kept !== operations) setBuffer(kept, Math.random())
  }

  return {
    operationsBuffer: new OperationsBuffer(data, hash),
    addToBuffer,
    addOperationsToBuffer,
    removeConditionOperationsOfQuestionsFromBuffer,
    clearBuffer,
    setBuffer,
  }
}
