import { Input } from 'components/UIComponents'
export const CssClasses = ({ cssclass: { value }, update }) => {
  return (
    <>
      <Input
        dataTestId="css-classes-attribute-input"
        onChange={({ target: { value } }) => update({ value })}
        value={value}
        labelText={'CSS class(es)'}
      />
    </>
  )
}
