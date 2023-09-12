import { ToggleButtons } from 'components/UIComponents'

export const MonthDisplayStyle = ({ monthDisplay: { value }, update }) => (
  <ToggleButtons
    id="monthDisplay"
    labelText="Month Display Style"
    value={value || false}
    onChange={(value) => update({ value })}
    toggleOptions={[
      { name: 'Short', value: 'short' },
      { name: 'Full', value: 'full' },
      { name: 'Numbers', value: 'numbers' },
    ]}
  />
)
