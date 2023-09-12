import { ToggleButtons } from 'components/UIComponents'

// Todo: check the correct attribute value/key name
export const DisplayChart = ({ publicStatistics: { value }, update }) => {
  return (
    <ToggleButtons
      labelText={'Display chart'}
      value={value || false}
      onChange={(value) => update({ value })}
    />
  )
}
