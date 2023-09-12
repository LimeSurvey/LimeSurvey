import { ToggleButtons } from 'components/UIComponents'

export const SameHeight = ({ sameHeight: { value }, update }) => (
  <ToggleButtons
    id="dropdownBox"
    labelText="Display dropdown boxes"
    value={value || false}
    onChange={(value) => update({ value })}
  />
)
