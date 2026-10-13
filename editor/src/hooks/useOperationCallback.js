import { useCallback } from 'react'
import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

export const useOperationCallback = () => {
  // eslint-disable-next-line no-unused-vars
  const { data: subscriptionMap } = useQuery({
    queryKey: [STATES.OPERATION_FINISH_SUBSCRIPTIONS],
    initialData: new Map(),
    queryFn: () => [],
    staleTime: Infinity,
  })

  const subscribe = useCallback(
    ({ entity, operation, callback, onError, once = true }) => {
      const key = `${entity}_${operation}`
      const subscription = { callback, onError, once }

      const currentSubscriptions = queryClient.getQueryData([
        STATES.OPERATION_FINISH_SUBSCRIPTIONS,
      ])

      if (currentSubscriptions.has(key)) {
        currentSubscriptions.get(key).push(subscription)
      } else {
        currentSubscriptions.set(key, [subscription])
      }

      queryClient.setQueryData(
        [STATES.OPERATION_FINISH_SUBSCRIPTIONS],
        currentSubscriptions
      )
    },
    []
  )

  const triggerCallbacks = useCallback((operations, results) => {
    if (!operations || operations.length === 0) return

    const currentSubscriptions = queryClient.getQueryData([
      STATES.OPERATION_FINISH_SUBSCRIPTIONS,
    ])

    // Operations rejected by the server get onError instead of callback
    const failedOperations = [
      ...(results?.validationErrors || []),
      ...(results?.exceptionErrors || []),
    ]

    operations.forEach((operation) => {
      const key = `${operation.entity}_${operation.op}`

      if (currentSubscriptions.has(key)) {
        const matchingSubscriptions = currentSubscriptions.get(key)
        const failedOperation = failedOperations.find(
          (failed) =>
            String(failed.id) === String(operation.id) &&
            failed.entity === operation.entity &&
            failed.op === operation.op
        )

        matchingSubscriptions.forEach((subscription) => {
          if (!failedOperation) {
            subscription.callback(results.extras)
          } else if (subscription.onError) {
            subscription.onError(failedOperation)
          }
        })

        const remainingSubscriptions = matchingSubscriptions.filter(
          (subscription) => !subscription.once
        )

        if (remainingSubscriptions.length > 0) {
          currentSubscriptions.set(key, remainingSubscriptions)
        } else {
          currentSubscriptions.delete(key)
        }

        queryClient.setQueryData(
          [STATES.OPERATION_FINISH_SUBSCRIPTIONS],
          currentSubscriptions
        )
      }
    })
  }, [])

  return {
    subscribeToOperationFinish: subscribe,
    triggerCallbacks,
  }
}
