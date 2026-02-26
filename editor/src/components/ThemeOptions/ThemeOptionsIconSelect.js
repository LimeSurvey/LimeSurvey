import classNames from 'classnames'
import { DropdownIcon } from 'components/icons'
import { useClickOutside } from 'hooks'
import React, { useCallback, useMemo, useRef, useState } from 'react'

const ThemeOptionsIconSelect = ({
  options = [],
  value = '',
  onChange = () => {},
  update = () => {},
}) => {
  const customSelectRef = useRef()
  const [isOpen, toggle] = useState(false)

  const close = useCallback(() => toggle(false), [])
  const open = useCallback(() => toggle(true), [])

  useClickOutside(customSelectRef, close)

  const unicodeChar = useMemo(
    () => String.fromCharCode(parseInt(value, 16)),
    [value]
  )

  const handleSelect = (newValue) => {
    onChange(newValue)
    update(newValue)
    close()
  }

  return (
    <div className="icon-select-container">
      <div className="swatch-container" onClick={open}>
        <div className="icon-label-padding">
          <i className="fa icon-preview">{unicodeChar}</i>
        </div>
        <DropdownIcon />
      </div>
      {isOpen && (
        <div className="icon-select-dropdown" ref={customSelectRef}>
          {options.map((option) => (
            <div
              key={option.value}
              className={classNames('icon-select-option', {
                selected: option.value === value,
              })}
              onClick={() => handleSelect(option.value)}
            >
              <i className="fa icon-preview me-2">
                {String.fromCharCode(parseInt(option.value, 16))}
              </i>
              <span>{option.label}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

export default ThemeOptionsIconSelect
