import { useState, useEffect } from 'react'

import { Input } from 'components'

export const ConstantInput = ({ index, condition, updateCondition }) => {
  const sanitizedValue = condition.value ?? '' // null not valid value
  const [value, setValue] = useState(sanitizedValue)

  useEffect(() => {
    if (condition.value === null) {
      updateCondition(index, 'value', '')
      setValue('')
    } else if (condition.value !== value) {
      setValue(sanitizedValue)
    }
  }, [condition.value])

  const handleChange = (e) => {
    const val = e.target.value
    setValue(val)
    updateCondition(index, 'value', val)
  }

  return (
    <Input
      type="text"
      placeholder={t('Value')}
      value={condition.value}
      onChange={handleChange}
      className="mb-3"
    />
  )
}
