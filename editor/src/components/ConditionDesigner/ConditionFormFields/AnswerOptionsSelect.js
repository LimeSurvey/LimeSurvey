import React, { useMemo } from 'react'

import { Select } from 'components'

export const AnswerOptionsSelect = ({ index, condition, updateCondition }) => {
  const options = useMemo(
    () =>
      condition.answers.map(({ value, label }) => ({
        value: String(value),
        label,
      })),
    [condition.answers]
  )

  const selectedOption = useMemo(() => {
    // Only treat `null` and `undefined` as "no selection"
    if (condition.value == null) return null

    // Handle empty string as a valid selection
    if (condition.value === '') {
      return options.find((opt) => opt.value === '') || null
    }

    // Split and filter (but keep empty strings if explicitly selected)
    const selectedValues = condition.value.toString().split(',')
    const filteredOptions = options.filter(({ value }) =>
      selectedValues.includes(value)
    )

    return condition.cid ? filteredOptions[0] : filteredOptions
  }, [options, condition.value, condition.cid])

  const handleChange = (selected) => {
    let newValue

    if (Array.isArray(selected)) {
      newValue =
        selected.length > 0
          ? selected.map(({ value }) => value).join(',')
          : null
    } else {
      newValue = selected?.value ?? null
    }

    updateCondition(index, 'value', newValue)
  }

  return (
    <Select
      options={options}
      value={
        !selectedOption
          ? null
          : condition.cid
            ? selectedOption?.value
            : selectedOption
      }
      onChange={handleChange}
      placeholder={t('Predefined')}
      className="mb-3"
      isMultiselect={!condition.cid}
    />
  )
}
