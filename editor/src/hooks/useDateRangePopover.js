import { useLayoutEffect, useRef, useState } from 'react'

import { useElementClick } from './useElementClick'

export const useDateRangePopover = () => {
  const [showPicker, setShowPicker] = useState(false)
  const [position, setPosition] = useState({ top: 0, left: 0 })
  const triggerRef = useRef(null)
  const containerRef = useRef(null)

  const closePicker = () => setShowPicker(false)
  const clickRef = useElementClick(closePicker)

  const openPicker = () => {
    const rect = triggerRef.current.getBoundingClientRect()
    const positionOffset = 120

    setPosition({
      top: rect.bottom - positionOffset,
      left: rect.left - positionOffset,
    })
    setShowPicker(true)
  }

  useLayoutEffect(() => {
    if (!showPicker || !containerRef.current) return

    const { width, height } = containerRef.current.getBoundingClientRect()
    const margin = 8

    setPosition((pos) => {
      const left = Math.max(
        margin,
        Math.min(pos.left, window.innerWidth - width - margin)
      )
      const top = Math.max(
        margin,
        Math.min(pos.top, window.innerHeight - height - margin)
      )
      return left === pos.left && top === pos.top ? pos : { top, left }
    })
  }, [showPicker, position])

  return {
    showPicker,
    openPicker,
    closePicker,
    position,
    triggerRef,
    containerRef,
    clickRef,
  }
}
