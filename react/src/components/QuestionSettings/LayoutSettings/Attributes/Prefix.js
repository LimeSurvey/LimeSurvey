import { Input } from 'components/UIComponents'

export const Prefix = ({ prefix: { value }, update }) => {
  return (
    <>
      <Input
        dataTestId="prefix-attribute-input"
        onChange={({ target: { value } }) => update({ value })}
        value={value || ''}
        labelText="Prefix"
      />
    </>
  )
}
