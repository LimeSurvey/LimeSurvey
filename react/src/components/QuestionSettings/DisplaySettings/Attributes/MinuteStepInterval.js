import React from 'react'
import { Input } from 'components/UIComponents'

export const MinuteStepInterval = ({
  minuteStepInterval: { value },
  update,
}) => (
  <Input
    dataTestId="minute-step-interval"
    onChange={({ target: { value } }) => update({ value })}
    value={value || ''}
    labelText="Minute step interval"
    type="number"
    max={30}
    min={1}
  />
)
