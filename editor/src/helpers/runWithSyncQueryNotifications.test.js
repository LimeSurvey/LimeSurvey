import {
  notifyManager,
  QueryClient,
  QueryObserver,
} from '@tanstack/react-query'

import { runWithSyncQueryNotifications } from './runWithSyncQueryNotifications'

const QUERY_KEY = ['sync-notifications-test']

const setup = () => {
  const queryClient = new QueryClient()
  queryClient.setQueryData(QUERY_KEY, ['a', 'b'])

  const listener = jest.fn()
  const observer = new QueryObserver(queryClient, {
    queryKey: QUERY_KEY,
    enabled: false,
  })
  // Subscribe the same way useQuery does, so the listener goes through the
  // notifyManager scheduler like a component re-render would.
  const unsubscribe = observer.subscribe(notifyManager.batchCalls(listener))

  return { queryClient, listener, unsubscribe }
}

describe('runWithSyncQueryNotifications', () => {
  test('notifies query observers before the callback returns', () => {
    const { queryClient, listener, unsubscribe } = setup()

    runWithSyncQueryNotifications(() => {
      queryClient.setQueryData(QUERY_KEY, ['b', 'a'])
      expect(listener).toHaveBeenCalledTimes(1)
    })

    expect(listener.mock.calls[0][0].data).toEqual(['b', 'a'])
    unsubscribe()
  })

  test('restores deferred notifications afterwards, even if the callback throws', async () => {
    const { queryClient, listener, unsubscribe } = setup()

    expect(() =>
      runWithSyncQueryNotifications(() => {
        throw new Error('drop failed')
      })
    ).toThrow('drop failed')

    queryClient.setQueryData(QUERY_KEY, ['b', 'a'])
    expect(listener).not.toHaveBeenCalled()

    await new Promise((resolve) => setTimeout(resolve, 0))
    expect(listener).toHaveBeenCalledTimes(1)
    unsubscribe()
  })

  test('returns the callback result', () => {
    expect(runWithSyncQueryNotifications(() => 42)).toBe(42)
  })
})
