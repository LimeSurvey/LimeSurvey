import { Direction } from 'react-range'

import { SettingsWrapper } from 'components/UIComponents'

import { SliderOrientation, UseSlider } from './Attributes'

export const SliderSettings = ({
  question: {
    attributes: { useSlider = false, sliderOrientation = Direction.Right } = {},
  } = {},
  handleUpdate,
  isAdvanced = false,
}) => {
  return (
    <SettingsWrapper title="Slider" isAdvanced={isAdvanced}>
      <UseSlider
        useSlider={useSlider}
        update={(changes) => {
          handleUpdate({ useSlider: changes.value })
        }}
      />
      <div className="mt-3">
        <SliderOrientation
          sliderOrientation={sliderOrientation}
          update={(changes) => {
            handleUpdate({ sliderOrientation: changes.value })
          }}
        />
      </div>
    </SettingsWrapper>
  )
}
