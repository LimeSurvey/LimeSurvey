import { Input } from 'components/UIComponents'

export const MaximumDate = ({ maxAnswer: { value }, update }) => {
  return (
    <Input
      dataTestId="max-date-input"
      onChange={({ target: { value } }) => update({ value })}
      value={value || ''}
      labelText="Maximum date"
      type="date"
    />
  )
}
