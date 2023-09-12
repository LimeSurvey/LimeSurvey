import { Direction } from 'react-range'

import { ToggleButtons } from 'components/UIComponents'

// Todo: check the correct attribute value/key name
export const SliderOrientation = ({ sliderOrientation, update }) => {
  return (
    <>
      <ToggleButtons
        name="slider-orientation-attribute"
        id={'slider-orientation-attribute'}
        value={sliderOrientation}
        labelText="Slider orientation"
        onChange={(value) => update({ value })}
        toggleOptions={[
          { name: 'Horizontal', value: Direction.Right },
          { name: 'Vertical', value: Direction.Down },
        ]}
      />
    </>
  )
}
