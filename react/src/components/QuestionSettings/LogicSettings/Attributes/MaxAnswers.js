import { Input } from 'components/UIComponents'

// Todo: check the correct attribute value/key name
export const MaxAnswers = ({ maxAnswer: { value }, update }) => {
  return (
    <>
      <Input
        dataTestId="max-answers-input"
        onChange={({ target: { value } }) => update({ value })}
        value={value || ''}
        labelText="Maximum answers"
        type="number"
      />
    </>
  )
}
