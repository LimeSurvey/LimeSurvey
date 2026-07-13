import React from 'react'

import { ToggleButtons } from 'components'

const checkedOptions = () => [
  { name: t('Checked'), value: 'Y' },
  { name: t('Not checked'), value: 'N' },
]

// Multiple-choice sub-question → Checked / Not checked.
export const CheckedToggle = ({ id, value, onChange }) => (
  <ToggleButtons
    id={id}
    toggleOptions={checkedOptions()}
    value={value}
    onChange={onChange}
  />
)
