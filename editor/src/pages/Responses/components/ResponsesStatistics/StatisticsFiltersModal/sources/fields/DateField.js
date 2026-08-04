import React, { useEffect, useLayoutEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs'
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider'
import { DateCalendar } from '@mui/x-date-pickers/DateCalendar'

import { DateIcon } from 'components/icons'
import { dayJsHelper } from 'helpers'

// A plain input showing a placeholder ("Start date" / "End date") and the chosen
// date; clicking the input or the calendar icon opens MUI's calendar. The
// calendar is portaled to <body> and positioned with fixed coords so it floats
// above the scrollable modal instead of being clipped by it.
export const DateField = ({ value, placeholder, onChange }) => {
  const [open, setOpen] = useState(false)
  const [position, setPosition] = useState({ top: 0, left: 0 })
  const anchorRef = useRef(null)
  const popoverRef = useRef(null)
  const selected = value ? dayJsHelper(value) : null

  const updatePosition = () => {
    const anchor = anchorRef.current
    if (!anchor) return
    const rect = anchor.getBoundingClientRect()
    const margin = 4
    // Flip above the input when there isn't room for the calendar below.
    const popoverHeight = popoverRef.current?.offsetHeight ?? 360
    const spaceBelow = window.innerHeight - rect.bottom
    const openUp = spaceBelow < popoverHeight + margin
    const top = openUp
      ? Math.max(8, rect.top - popoverHeight - margin)
      : rect.bottom + margin
    setPosition({ top, left: rect.left })
  }

  // Keep the popover anchored while open (re-position on scroll/resize).
  useLayoutEffect(() => {
    if (!open) return undefined
    updatePosition()
    const reposition = () => updatePosition()
    window.addEventListener('scroll', reposition, true)
    window.addEventListener('resize', reposition)
    return () => {
      window.removeEventListener('scroll', reposition, true)
      window.removeEventListener('resize', reposition)
    }
  }, [open])

  // Close on click outside the input or the (portaled) popover.
  useEffect(() => {
    if (!open) return undefined
    const handleClickOutside = (event) => {
      const target = event.target
      const insideAnchor = anchorRef.current?.contains(target)
      const insidePopover = popoverRef.current?.contains(target)
      if (!insideAnchor && !insidePopover) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [open])

  const handleSelect = (date) => {
    onChange(date ? date.toJSON() : null)
    setOpen(false)
  }

  return (
    <div className="responses-statistics-filters-date" ref={anchorRef}>
      <input
        type="text"
        readOnly
        className="form-control responses-statistics-filters-date-input"
        placeholder={placeholder}
        value={selected ? selected.format('MM/DD/YYYY') : ''}
        onClick={() => setOpen((isOpen) => !isOpen)}
      />
      <button
        type="button"
        className="responses-statistics-filters-date-icon"
        onClick={() => setOpen((isOpen) => !isOpen)}
        aria-label={placeholder}
      >
        <DateIcon />
      </button>
      {open &&
        createPortal(
          <div
            ref={popoverRef}
            className="responses-statistics-filters-date-popover"
            style={{ top: position.top, left: position.left }}
          >
            <LocalizationProvider dateAdapter={AdapterDayjs}>
              <DateCalendar value={selected} onChange={handleSelect} />
            </LocalizationProvider>
          </div>,
          document.body
        )}
    </div>
  )
}
