import { useEffect, useRef, useState } from 'react'

export const useIsInViewport = (
  externalRef = null,
  {
    initialInView = true,
    rootMargin = '1000px 0px 1000px 0px',
    onChange,
  } = {}
) => {
  const internalRef = useRef(null)
  // Defaults to `true` so consumers that render-until-proven-offscreen behave correctly
  const [isInView, setIsInView] = useState(initialInView)

  const ref = externalRef || internalRef

  // Keep the latest onChange so the observer (set up once) always calls the
  // current closure — avoids acting on stale state (e.g. hasNextPage).
  const onChangeRef = useRef(onChange)
  onChangeRef.current = onChange

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          setIsInView(entry.isIntersecting)
          onChangeRef.current?.(entry.isIntersecting)
        })
      },
      {
        // Consumers only need a binary in-view signal, so fire once on enter
        // and once on leave rather than at every 10% visibility step.
        threshold: 0,
        rootMargin,
      }
    )

    if (ref.current) {
      observer.observe(ref.current)
    }

    return () => {
      if (ref.current) {
        observer.unobserve(ref.current)
      }
    }
  }, [ref, rootMargin])

  return [ref, isInView]
}
