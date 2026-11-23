import { useCallback, useLayoutEffect, useRef } from 'react'

/**
 * Returns a function that keeps the same identity across renders and always
 * calls the latest `callback`.
 *
 * Use it for event handlers passed to memoized children: A new handler on
 * every render would make them render again. Do not call the returned
 * function during rendering, it may still see the previous `callback` then.
 */
export const useEventCallback = (callback) => {
  const callbackRef = useRef(callback)

  useLayoutEffect(() => {
    callbackRef.current = callback
  })

  return useCallback((...args) => callbackRef.current(...args), [])
}
