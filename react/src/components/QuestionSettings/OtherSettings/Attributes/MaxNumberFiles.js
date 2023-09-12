import React from 'react'
import { Input } from 'components/UIComponents'

export const MaxNumberFiles = ({ maxNumberFiles: { value }, update, min }) => (
  <Input
    dataTestId="max-number-files"
    onChange={({ target: { value } }) => update({ value })}
    value={value || ''}
    labelText="Max number of files"
    type="number"
    min={min || 1}
  />
)
