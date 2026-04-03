import classNames from 'classnames'
import { DropdownIcon } from 'components/icons'
import { useClickOutside } from 'hooks'
import React, { useMemo, useRef, useState } from 'react'

const hexToChar = (hex) => String.fromCharCode(parseInt(hex, 16))

const ensureHashPrefix = (color) =>
  color && !color.startsWith('#') ? `#${color}` : color

const IconPreview = ({
  library,
  value,
  className = '',
  colorSwatch = false,
}) => {
  if (colorSwatch) {
    return (
      <span
        className={classNames('swatch', className)}
        style={{
          backgroundColor: ensureHashPrefix(value),
          marginRight: 5,
        }}
      />
    )
  }

  const iconClass = library === 'remixicon' ? 'ri-' : 'fa t'
  return (
    <i className={classNames(iconClass, 'icon-preview', className)}>
      {hexToChar(value)}
    </i>
  )
}

const ConditionalWrapper = ({ condition, wrapper, children }) =>
  condition ? wrapper(children) : children

const ThemeOptionsIconSelect = ({
  options = [],
  value = '',
  onChange = () => {},
  update = () => {},
  setting = {},
}) => {
  const customSelectRef = useRef()
  const [isOpen, toggle] = useState(false)

  const library = setting?.attribute?.library || ''
  const colorSwatch = setting?.attribute?.colorSwatch || false
  const dropdownOptionsArray = setting?.attribute?.dropdownoptionsArray

  const effectiveValue = (option) =>
    option.value === 'inherit' ? option.parentValue : option.value

  const close = () => toggle(false)
  const open = () => toggle(true)

  useClickOutside(customSelectRef, close)

  const handleSelect = (newValue) => {
    onChange(newValue)
    update(newValue)
    close()
  }

  const getHexValue = (selectedOption) => {
    if (!selectedOption) return value
    const match = dropdownOptionsArray?.optgroup[0]?.option?.find(
      (opt) => opt.value === selectedOption.label
    )
    return match ? match.attributes.hexValue : selectedOption.value
  }

  const resolveValue = (option) =>
    colorSwatch ? getHexValue(option) : option.value

  const selectedDisplayValue = useMemo(
    () =>
      colorSwatch
        ? getHexValue(
            options.find((opt) => opt.value === value || opt.label === value)
          )
        : value,
    [colorSwatch, dropdownOptionsArray, options, value]
  )

  return (
    <div className="icon-select-container">
      <div className="swatch-container" onClick={open}>
        <ConditionalWrapper
          condition={!colorSwatch}
          wrapper={(children) => (
            <div className="icon-label-padding">{children}</div>
          )}
        >
          <IconPreview
            library={library}
            value={selectedDisplayValue}
            colorSwatch={colorSwatch}
          />
        </ConditionalWrapper>
        <DropdownIcon />
      </div>
      {isOpen && (
        <div className="icon-select-dropdown" ref={customSelectRef}>
          {options.map((option) => (
            <div
              key={option.value}
              className={classNames('icon-select-option', {
                selected: option.value === effectiveValue(value),
              })}
              onClick={() => handleSelect(option.value)}
            >
              <IconPreview
                library={library}
                className="me-2"
                colorSwatch={colorSwatch}
                value={resolveValue(option)}
              />
              <span>{option.label}</span>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

export default ThemeOptionsIconSelect
