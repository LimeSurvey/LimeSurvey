import { Input } from 'components'

// Todo: check the correct attribute value/key 📛
export const RegExp = ({ reg_exp: { value }, update }) => {
  return (
    <>
      <p className="right-side-bar-header">RegExp</p>
      <Input
        dataTestId="reg-exp-attribute-input"
        onChange={({ target: { value } }) => update({ value })}
        value={value}
      />
    </>
  )
}
