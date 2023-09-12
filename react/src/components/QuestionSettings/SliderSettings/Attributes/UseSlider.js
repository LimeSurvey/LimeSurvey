import { ToggleButtons } from 'components/UIComponents'

// Todo: check the correct attribute value/key name
export const UseSlider = ({ useSlider, update }) => {
  return (
    <>
      <ToggleButtons
        name="use-slider-attribute"
        id={'use-slider-attribute'}
        value={useSlider}
        labelText="Use slider"
        onChange={(value) => update({ value })}
      />
    </>
  )
}
