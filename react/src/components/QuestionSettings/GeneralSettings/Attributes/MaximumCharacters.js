import { Input } from 'components'

export const MaximumCharacters = ({ maximum_chars: { value }, update }) => {
  return (
    <>
      <Input
        labelText="Maximum characters"
        dataTestId="maximum-characters-attribute-input"
        type="number"
        onChange={({ target: { value } }) => update({ value })}
        value={value || ''}
        max={Infinity}
        allowEmpty={true}
      />
    </>
  )
}
