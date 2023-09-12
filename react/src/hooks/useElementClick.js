import { useRef, useEffect } from 'react'

export const useElementClick = (callback, isOutSide = true) => {
  const ref = useRef()

  useEffect(() => {
    const handleClick = (event) => {
      if (ref.current) {
        // when click outside element
        if (isOutSide && !ref.current.contains(event.target)) {
          callback()
          // when click inside element
        } else if (!isOutSide && ref.current.contains(event.target)) {
          callback()
        }
      }
    }

    document.addEventListener('click', handleClick, true)

    return () => {
      document.removeEventListener('click', handleClick, true)
    }
  }, [ref, callback, isOutSide])

  return ref
}
