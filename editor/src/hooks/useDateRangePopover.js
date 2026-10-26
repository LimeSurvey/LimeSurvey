import { useEffect, useLayoutEffect, useRef, useState } from 'react'

import { useElementClick } from './useElementClick'

const FOCUSABLE_SELECTOR =
  'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [href], [tabindex]:not([tabindex="-1"])'

// positionOffset: how far up and left of the trigger the popover opens.
export const useDateRangePopover = (
  onDismiss,
  { positionOffset = 120 } = {}
) => {
  const [showPicker, setShowPicker] = useState(false)
  const [position, setPosition] = useState({ top: 0, left: 0 })
  const triggerRef = useRef(null)
  const containerRef = useRef(null)
  const restoreFocusRef = useRef(false)

  const closePicker = () => {
    restoreFocusRef.current = true
    setShowPicker(false)
  }
  const dismiss = onDismiss ?? closePicker

  const clickRef = useElementClick(() => {
    dismiss()
    // Don't pull focus back from wherever the user just clicked.
    restoreFocusRef.current = false
  })

  const handleKeyDown = (e) => {
    if (e.key !== 'Escape') return
    e.stopPropagation()
    dismiss()
  }

  const openPicker = () => {
    const rect = triggerRef.current.getBoundingClientRect()

    setPosition({
      top: rect.bottom - positionOffset,
      left: rect.left - positionOffset,
    })
    setShowPicker(true)
  }

  const clampToViewport = () => {
    if (!containerRef.current) return

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
  }

  useLayoutEffect(() => {
    if (showPicker) clampToViewport()
  }, [showPicker, position])

  useEffect(() => {
    if (showPicker) {
      const container = containerRef.current
      ;(container?.querySelector(FOCUSABLE_SELECTOR) ?? container)?.focus()
    } else if (restoreFocusRef.current) {
      triggerRef.current?.focus()
    }
    restoreFocusRef.current = false
  }, [showPicker])

  useLayoutEffect(() => {
    const container = containerRef.current
    if (!showPicker || !container) return

    const observer = new ResizeObserver(clampToViewport)
    observer.observe(container)
    window.addEventListener('resize', clampToViewport)

    return () => {
      observer.disconnect()
      window.removeEventListener('resize', clampToViewport)
    }
  }, [showPicker])

  return {
    showPicker,
    openPicker,
    closePicker,
    position,
    triggerRef,
    containerRef,
    clickRef,
    handleKeyDown,
  }
}
