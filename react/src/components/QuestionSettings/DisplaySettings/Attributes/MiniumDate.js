import { Input } from 'components/UIComponents'

export const MiniumDate = ({ minAnswer: { value }, update }) => {
  return (
    <Input
      dataTestId="min-date-input"
      onChange={({ target: { value } }) => update({ value })}
      value={value || ''}
      labelText="Minimum date"
      type="date"
    />
  )
}
