import { useRef, useCallback } from 'react'

const useAnimationFrameDebounce = (fn, wait = 0) => {
  const frameId = useRef(null)
  const lastArgs = useRef([])

  const debounced = useCallback(
    (...args) => {
      if (frameId.current !== null) {
        cancelAnimationFrame(frameId.current)
      }

      const startTime = performance.now()
      lastArgs.current = args

      const loop = (timeNow) => {
        if (timeNow - startTime < wait) {
          frameId.current = requestAnimationFrame(loop)
          return
        }

        fn(...lastArgs.current)
        frameId.current = null
      }

      frameId.current = requestAnimationFrame(loop)
    },
    [fn, wait]
  )

  return debounced
}

export const useDebounce = (fn, wait = 0) => {
  return useAnimationFrameDebounce(fn, wait)
}
