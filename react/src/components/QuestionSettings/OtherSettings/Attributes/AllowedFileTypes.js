import React from 'react'
import { Input } from 'components/UIComponents'

export const AllowedFileTypes = ({ allowedFileTypes: { value }, update }) => (
  <Input
    dataTestId="allowed-file-types"
    onChange={({ target: { value } }) => update({ value })}
    value={value || ''}
    labelText="Allowed file types"
  />
)
