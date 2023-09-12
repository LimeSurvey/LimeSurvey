import { Input } from 'components'

export const FormFieldText = ({
  maximumChars,
  form_field_text: { value },
  update,
}) => {
  return (
    <Input
      dataTestId="form-field-text-attribute-input"
      onChange={({ target: { value } }) => update({ value })}
      value={value}
      labelText="Form field text"
      max={maximumChars}
    />
  )
}
