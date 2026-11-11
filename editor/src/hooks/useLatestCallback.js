import { useCallback, useRef } from 'react'

/**
 * A function with a stable identity that always calls the `callback` of the
 * latest render. For callbacks handed to something outliving the render, e.g.
 * a confirm dialog: When the user confirms, the survey may have changed (a
 * save replaced temp IDs), and the callback of the render that opened the
 * dialog would work on outdated data.
 * Only helps while the component stays mounted.
 *
 * @param {Function} callback
 * @returns {Function}
 */
export const useLatestCallback = (callback) => {
  const latest = useRef(callback)
  latest.current = callback

  return useCallback((...args) => latest.current(...args), [])
}
