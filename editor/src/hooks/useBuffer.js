import { useCallback } from 'react'
import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { OperationsBuffer, STATES } from 'helpers'

import { useErrors } from './useErrors'

export const useBuffer = () => {
  let { data } = useQuery({
    queryKey: [STATES.BUFFER],
    queryFn: () => [],
    cacheTime: Infinity,
    staleTime: Infinity,
  })

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

  return {
    operationsBuffer: new OperationsBuffer(data, hash),
    addToBuffer,
    clearBuffer,
    setBuffer,
  }
}
