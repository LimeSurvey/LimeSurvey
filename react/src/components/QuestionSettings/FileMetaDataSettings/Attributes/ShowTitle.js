import { ToggleButtons } from 'components/UIComponents'

export const ShowTitle = ({ showTitle: { value }, update }) => (
  <ToggleButtons
    id="show-title"
    labelText="Show Title"
    value={value || false}
    onChange={(value) => update({ value })}
  />
)
