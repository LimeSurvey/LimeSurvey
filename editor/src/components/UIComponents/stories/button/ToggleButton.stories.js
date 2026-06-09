import { useState } from 'react'
import { ToggleButtons } from '../../Buttons/ToggleButtons'

export default {
  title: 'UIComponents/Button/ToggleButton',
  component: ToggleButtons,
}

export function Basic(args) {
  const [value, setValue] = useState(-1)
  return (
    <div data-testid="toggle-buttons-toggle-group">
      <p className="d-none" data-testid="output">
        {JSON.stringify(value)}
      </p>
      <ToggleButtons
        id="toggle-buttons"
        value={value}
        onChange={(value) => setValue(value)}
        {...args}
      />
    </div>
  )
}
