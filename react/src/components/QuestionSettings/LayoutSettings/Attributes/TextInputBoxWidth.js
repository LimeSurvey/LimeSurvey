import { MINIMUM_INPUT_WIDTH_PERCENT } from 'helpers'
import { Input } from 'components/UIComponents'

export const TextInputBoxWidth = ({ text_input_width: { value }, update }) => {
  const handleOnChange = (value) => {
    if (value > 100) {
      value = 100
      update({ value: 100 })
    } else {
      update({ value })
    }
  }

  const handleOnBlur = (event) => {
    if (!event.currentTarget.value.trim()) {
      update({ value: 100 })
    } else if (event.currentTarget.value < MINIMUM_INPUT_WIDTH_PERCENT) {
      event.currentTarget.value = MINIMUM_INPUT_WIDTH_PERCENT
      update({ value: MINIMUM_INPUT_WIDTH_PERCENT })
    }
  }

  return (
    <>
      <Input
        dataTestId="text-input-box-width-attribute-input"
        type="number"
        value={value}
        placeholder="%"
        onChange={({ target: { value } }) => handleOnChange(value)}
        onBlur={handleOnBlur}
        max={100}
        min={15}
        labelText="Text input box width"
      />
    </>
  )
}
