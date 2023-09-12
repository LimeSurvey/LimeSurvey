import React from 'react'
import { Input } from 'components/UIComponents'

export const MinNumberFiles = ({ minNumberFiles: { value }, update, max }) => (
  <Input
    dataTestId="min-number-files"
    onChange={({ target: { value } }) => update({ value })}
    value={value || ''}
    labelText="Min number of files"
    type="number"
    max={max || 100}
    min={1}
  />
)
