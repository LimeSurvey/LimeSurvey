import React, { useCallback, useRef, useState } from 'react'
import { HexColorPicker } from 'react-colorful'

import { useClickOutside } from 'hooks'
import { DropdownIcon } from 'components/icons'

export const PopoverPicker = ({ color, onChange }) => {
  const popover = useRef()
  const [isOpen, toggle] = useState(false)

  const close = useCallback(() => toggle(false), [])
  const open = useCallback(() => toggle(true), [])

  useClickOutside(popover, close)

  return (
    <div className="picker">
      <div className="swatch-container" onClick={open}>
        <div className="swatch" style={{ backgroundColor: color }} />
        <DropdownIcon />
      </div>
      {isOpen && (
        <div className="popover" ref={popover}>
          <section className="custom-picker">
            <HexColorPicker color={color} onChange={onChange} />
          </section>
        </div>
      )}
    </div>
  )
}
