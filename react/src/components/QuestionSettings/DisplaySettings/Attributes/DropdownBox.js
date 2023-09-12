import { ToggleButtons } from 'components/UIComponents'

export const DropdownBox = ({ dropdownBox: { value }, update }) => (
  <ToggleButtons
    id="dropdownBox"
    labelText="Display dropdown boxes"
    value={value || false}
    onChange={(value) => update({ value })}
  />
)
