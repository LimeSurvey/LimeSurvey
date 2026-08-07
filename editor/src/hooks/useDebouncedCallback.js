import { useRef, useCallback, useEffect } from 'react'

export const useDebouncedCallback = (callback, delay = 500) => {
  const timeoutRef = useRef()

  const debouncedFn = useCallback(
    (...args) => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current)
      }
      timeoutRef.current = setTimeout(() => {
        callback(...args)
      }, delay)
    },
    [callback, delay]
  )

  useEffect(() => {
    return () => clearTimeout(timeoutRef.current)
  }, [])

  return debouncedFn
}
