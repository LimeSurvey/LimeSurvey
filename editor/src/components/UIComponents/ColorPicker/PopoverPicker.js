import React, { useCallback, useRef, useState } from 'react'
import { HexColorPicker } from 'react-colorful'

import { useClickOutside } from 'hooks'
import { DropdownIcon } from 'components/icons'

const expandHex = (hex) => {
  const short = /^#?([a-f\d])([a-f\d])([a-f\d])$/i.exec(hex)
  if (short)
    return `#${short[1]}${short[1]}${short[2]}${short[2]}${short[3]}${short[3]}`
  return hex
}

const hexToRgb = (hex) => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(
    expandHex(hex)
  )
  return result
    ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16),
      }
    : { r: 0, g: 0, b: 0 }
}

const rgbToHex = (r, g, b) =>
  '#' +
  [r, g, b]
    .map((v) => {
      const hex = Math.max(0, Math.min(255, v)).toString(16)
      return hex.length === 1 ? '0' + hex : hex
    })
    .join('')

export const PopoverPicker = ({ color, onChange }) => {
  const popover = useRef()
  const [isOpen, toggle] = useState(false)
  const [hexInput, setHexInput] = useState(color)

  const close = useCallback(() => toggle(false), [])
  const open = useCallback(() => toggle(true), [])

  useClickOutside(popover, close)

  const rgb = hexToRgb(color)

  const handlePickerChange = (newColor) => {
    setHexInput(newColor)
    onChange(newColor)
  }

  const handleHexChange = (e) => {
    const value = e.target.value
    setHexInput(value)
    if (/^#[a-f\d]{6}$/i.test(value)) {
      onChange(value.toLowerCase())
    } else if (/^#[a-f\d]{3}$/i.test(value)) {
      onChange(expandHex(value).toLowerCase())
    }
  }

  const handleRgbChange = (channel, value) => {
    const num = value === '' ? 0 : parseInt(value, 10)
    if (isNaN(num)) return
    const clamped = Math.max(0, Math.min(255, num))
    const newRgb = { ...rgb, [channel]: clamped }
    const hex = rgbToHex(newRgb.r, newRgb.g, newRgb.b)
    setHexInput(hex)
    onChange(hex)
  }

  return (
    <div className="picker">
      <div className="swatch-container" onClick={open}>
        <div className="swatch" style={{ backgroundColor: color }} />
        <DropdownIcon />
      </div>
      {isOpen && (
        <div className="popover" ref={popover}>
          <section className="custom-picker">
            <HexColorPicker color={color} onChange={handlePickerChange} />
            <div className="color-inputs">
              <div className="color-input-group color-input-hex">
                <label>{t('HEX')}</label>
                <input
                  type="text"
                  value={hexInput}
                  onChange={handleHexChange}
                />
              </div>
              <div className="color-input-group">
                <label>R</label>
                <input
                  type="number"
                  min="0"
                  max="255"
                  value={rgb.r}
                  onChange={(e) => handleRgbChange('r', e.target.value)}
                />
              </div>
              <div className="color-input-group">
                <label>G</label>
                <input
                  type="number"
                  min="0"
                  max="255"
                  value={rgb.g}
                  onChange={(e) => handleRgbChange('g', e.target.value)}
                />
              </div>
              <div className="color-input-group">
                <label>B</label>
                <input
                  type="number"
                  min="0"
                  max="255"
                  value={rgb.b}
                  onChange={(e) => handleRgbChange('b', e.target.value)}
                />
              </div>
            </div>
          </section>
        </div>
      )}
    </div>
  )
}
