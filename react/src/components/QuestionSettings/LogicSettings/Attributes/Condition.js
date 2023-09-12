import { Input } from 'components/UIComponents'

// Todo: check the correct attribute value/key name
export const Condition = ({ condition: { value }, update }) => {
  return (
    <>
      <Input
        dataTestId="condition-attribute-input"
        onChange={({ target: { value } }) => update({ value })}
        value={value || ''}
        labelText="Condition"
      />
    </>
  )
}
