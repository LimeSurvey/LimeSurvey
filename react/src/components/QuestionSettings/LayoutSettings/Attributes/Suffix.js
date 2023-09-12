import { Input } from 'components/UIComponents'

export const Suffix = ({ suffix: { value }, update }) => {
  return (
    <>
      <Input
        dataTestId="suffix-attribute-input"
        onChange={({ target: { value } }) => update({ value })}
        value={value}
        labelText="Suffix"
      />
    </>
  )
}
