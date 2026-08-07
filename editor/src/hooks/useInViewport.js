import { useEffect, useRef, useState } from 'react'

export const useIsInViewport = (externalRef = null) => {
  const internalRef = useRef(null)
  const [isInView, setIsInView] = useState(true)

  const ref = externalRef || internalRef

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          setIsInView(entry.isIntersecting)
        })
      },
      {
        threshold: Array.from({ length: 11 }, (_, i) => i / 10), // 10% threshold for each intersection
        rootMargin: '1000px 0px 1000px 0px',
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
  }, [ref])

  return [ref, isInView]
}
