import React from 'react'
import { Input } from 'components/UIComponents'

export const MaximumFileSizeAllowed = ({
  maximumFileSizeAllowed: { value },
  update,
}) => (
  <Input
    type="number"
    dataTestId="maximum-file-size"
    onChange={({ target: { value } }) => update({ value })}
    value={value || '10'}
    labelText="Maximum file size allowed (in KB)"
    min={1}
  />
)
