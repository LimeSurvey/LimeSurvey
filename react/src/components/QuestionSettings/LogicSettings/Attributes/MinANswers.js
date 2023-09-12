import { Input } from 'components/UIComponents'

// Todo: check the correct attribute value/key name
export const MinAnswers = ({ minAnswer: { value }, update }) => {
  return (
    <>
      <Input
        dataTestId="min-answers-input"
        onChange={({ target: { value } }) => update({ value })}
        value={value || ''}
        labelText="Minimum answers"
        type="number"
      />
    </>
  )
}
