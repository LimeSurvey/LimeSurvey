import classNames from 'classnames'
import { InputRange } from 'components/UIComponents'
import { getStringPartsUsingSeperator, getAttributeValue } from 'helpers'
import React from 'react'
import { Direction } from 'react-range'

const MultipleChoiceNumericalSlider = ({
  attributes,
  text,
  defaultValue,
  onChange,
  participantMode = false,
}) => {
  const {
    slider_orientation,
    slider_min,
    slider_max,
    slider_accuracy,
    slider_reversed,
    slider_middlestart,
    slider_showminmax,
    slider_separator,
    slider_default,
    slider_default_set,
    slider_handle,
    slider_custom_handle,
  } = attributes

  const orientationValue = getAttributeValue(slider_orientation) ?? '0'
  const isOrientationReversed = getAttributeValue(slider_reversed) === '1'
  const isOrientationHorizontal = orientationValue === '0'
  const orientation = isOrientationHorizontal
    ? isOrientationReversed
      ? Direction.Left
      : Direction.Right
    : isOrientationReversed
      ? Direction.Up
      : Direction.Down

  const min = getAttributeValue(slider_min) || 0
  const max = getAttributeValue(slider_max) || 100
  const accuracy = getAttributeValue(slider_accuracy) || 1

  const value = defaultValue
    ? +defaultValue
    : getAttributeValue(slider_middlestart) === '1'
      ? (+max + +min) / 2
      : +min

  const getPossibleValueFromMinMax = (val) => {
    return val > +max ? +max : val < +min ? +min : val
  }
  const intialValue = getPossibleValueFromMinMax(value)
  const shouldUseInitialValue = getAttributeValue(slider_default_set) === '1'
  const userInitialValue = getAttributeValue(slider_default)

  const sliderValue =
    shouldUseInitialValue && userInitialValue
      ? getPossibleValueFromMinMax(+userInitialValue)
      : intialValue

  const showMinMax = getAttributeValue(slider_showminmax) === '1'

  const { leftText, rightText } = getStringPartsUsingSeperator(
    text,
    getAttributeValue(slider_separator)
  )

  const leftFinalText = isOrientationReversed ? rightText : leftText
  const rightFinalText = isOrientationReversed ? leftText : rightText

  const sliderThumbType = getAttributeValue(slider_handle)
  const sliderCustomUnicode = getAttributeValue(slider_custom_handle)

  return (
    <div
      className={classNames({
        'd-flex': showMinMax,
        'd-flex flex-column': isOrientationHorizontal,
        'd-flex flex-row': !isOrientationHorizontal,
      })}
    >
      {!isOrientationHorizontal && (leftText || rightText) && (
        <div className="d-flex flex-column justify-content-between min-h-100 me-2">
          <div className="text-muted small">{leftFinalText}</div>
          <div className="text-muted small">{rightFinalText}</div>
        </div>
      )}
      <InputRange
        showInput={false}
        direction={orientation ? orientation : Direction.Right}
        min={+min}
        max={+max}
        step={+accuracy}
        value={
          participantMode ? (Number(defaultValue) ?? sliderValue) : sliderValue
        }
        showMinMax={showMinMax}
        reverseMinMax={isOrientationReversed}
        thumbType={sliderThumbType}
        customUnicode={sliderCustomUnicode}
        onChange={onChange}
      />
      {isOrientationHorizontal && (leftText || rightText) && (
        <div className="d-flex justify-content-between w-100 mt-2">
          <div className="text-muted small">{leftFinalText}</div>
          <div className="text-muted small">{rightFinalText}</div>
        </div>
      )}
    </div>
  )
}

export default MultipleChoiceNumericalSlider
