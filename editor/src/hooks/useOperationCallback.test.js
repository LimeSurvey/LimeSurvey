import { renderHook } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

import { useOperationCallback } from './useOperationCallback'

const wrapper = ({ children }) => (
  <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
)

const operation = { id: 123, entity: 'surveyStatus', op: 'update' }

describe('useOperationCallback', () => {
  beforeEach(() => {
    queryClient.setQueryData([STATES.OPERATION_FINISH_SUBSCRIPTIONS], new Map())
  })

  const subscribeAndTrigger = (results) => {
    const callback = jest.fn()
    const onError = jest.fn()
    const { result } = renderHook(() => useOperationCallback(), { wrapper })

    result.current.subscribeToOperationFinish({
      entity: operation.entity,
      operation: operation.op,
      callback,
      onError,
    })
    result.current.triggerCallbacks([operation], results)

    return { callback, onError }
  }

  test('calls callback for a successful operation', () => {
    const { callback, onError } = subscribeAndTrigger({
      operationsApplied: 1,
      extras: { expire: '2026-10-13' },
    })

    expect(callback).toHaveBeenCalledWith({ expire: '2026-10-13' })
    expect(onError).not.toHaveBeenCalled()
  })

  test('calls onError instead of callback for a failed operation', () => {
    const failed = {
      ...operation,
      id: '123',
      error: 'Can not activate without expiration date.',
    }
    const { callback, onError } = subscribeAndTrigger({
      operationsApplied: 0,
      exceptionErrors: [failed],
    })

    expect(callback).not.toHaveBeenCalled()
    expect(onError).toHaveBeenCalledWith(failed)
    expect(
      queryClient
        .getQueryData([STATES.OPERATION_FINISH_SUBSCRIPTIONS])
        .has(`${operation.entity}_${operation.op}`)
    ).toBe(false)
  })
})
