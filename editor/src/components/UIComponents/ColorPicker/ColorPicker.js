import React, { useCallback, useEffect, useState, useRef } from 'react'

import { PopoverPicker } from './PopoverPicker'

const ColorPicker = ({ onChange = () => {}, update = () => {}, value }) => {
  const [color, setColor] = useState(value)
  const timerRef = useRef(null)

  const debounceColorUpdate = useCallback(
    (newColor) => {
      if (timerRef.current) {
        clearTimeout(timerRef.current)
      }
      timerRef.current = setTimeout(() => {
        onChange(newColor)
        update(newColor)
        timerRef.current = null
      }, 400)
    },
    [onChange, update]
  )

  const onColorChange = useCallback(
    (newColor) => {
      setColor(newColor)
      debounceColorUpdate(newColor)
    },
    [debounceColorUpdate]
  )

  useEffect(() => {
    setColor(value)
  }, [value])

  useEffect(() => {
    return () => {
      if (timerRef.current) {
        clearTimeout(timerRef.current)
        timerRef.current = null
      }
    }
  }, [])

  return <PopoverPicker color={color} onChange={onColorChange} />
}

export default ColorPicker
