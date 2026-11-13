import React, { useEffect, useState } from 'react'
import { Direction, Range, getTrackBackground } from 'react-range'
import { Form } from 'react-bootstrap'

import { Input } from '../Input/Input'
import classNames from 'classnames'
import { getThumbStyle, THUMB_TYPES } from 'helpers'

// Colors of the filled and the empty part of the track.
const TRACK_COLORS = ['#8146F6', '#DDDEE8']

/**
 * Returns the size of the thumb along the track, see getThumbStyle.
 *
 * @param {string} thumbType one of THUMB_TYPES
 * @returns {number} the size in px
 */
const getThumbSize = (thumbType) => (thumbType === THUMB_TYPES.CUSTOM ? 16 : 12)

export const InputRange = ({
  labelText,
  min = 0,
  max = 100,
  step = 1,
  value = 0,
  onChange = () => {},
  showInput = true,
  direction = Direction.Right,
  showMinMax = false,
  reverseMinMax = false,
  thumbType = THUMB_TYPES.CIRCLE,
  customUnicode = '',
  // When set, the label is linked to the number input.
  id,
}) => {
  const [values, setValues] = useState([value >= min ? value : min])
  // Don't report the initial value as a change.
  const isInitialValue = React.useRef(true)
  const [horizontal, setHorizontal] = useState(
    direction === Direction.Right || direction === Direction.Left
  )
  const inputRef = React.useRef(null)

  const handleOnChange = (values) => {
    setValues([...values])
  }

  useEffect(() => {
    if (isInitialValue.current) {
      isInitialValue.current = false
      return
    }
    onChange(values)
  }, [values[0]])

  useEffect(() => {
    setHorizontal(direction === Direction.Right || direction === Direction.Left)
  }, [direction])

  const renderThumbContent = () => {
    if (thumbType === THUMB_TYPES.CUSTOM && !!customUnicode) {
      const unicodeChar = String.fromCharCode(parseInt(customUnicode, 16))
      return (
        <i className="fa" style={{ fontFamily: 'FontAwesome' }}>
          {unicodeChar}
        </i>
      )
    }
    return null
  }

  return (
    <div className="input-range" data-testid="input-range">
      {labelText && <Form.Label htmlFor={id}>{labelText}</Form.Label>}
      <div className="d-flex align-items-center w-100">
        <div
          className={classNames('flex-grow-1 position-relative', {
            'd-flex': showMinMax && !horizontal,
          })}
        >
          {showMinMax && horizontal && (
            <div className="d-flex justify-content-between w-100 my-2">
              <div className="text-muted small">
                {reverseMinMax ? max : min}
              </div>
              <div className="text-muted small">
                {reverseMinMax ? min : max}
              </div>
            </div>
          )}
          {showMinMax && !horizontal && (
            <div className="d-flex flex-column justify-content-between min-h-100 mx-2">
              <div className="text-muted small">
                {reverseMinMax ? max : min}
              </div>
              <div className="text-muted small">
                {reverseMinMax ? min : max}
              </div>
            </div>
          )}
          <Range
            values={values}
            step={step}
            min={min}
            max={max}
            direction={direction}
            onChange={handleOnChange}
            data-testid="input-range-slider"
            // The slider measures the inner track, which is shorter than the
            // visible line by half a thumb on each end (see input-range.scss):
            // At min and max, the thumb ends where the line ends. The visible
            // ends past the inner track get the fill and the empty color.
            renderTrack={({ props, children }) => (
              <div
                className={
                  'input-range-track' + (horizontal ? '' : ' vertical')
                }
                onMouseDown={props.onMouseDown}
                onTouchStart={props.onTouchStart}
                style={{
                  ...props.style,
                  '--input-range-thumb-inset': `${getThumbSize(thumbType) / 2}px`,
                  'background': `linear-gradient(${direction}, ${TRACK_COLORS[0]} 50%, ${TRACK_COLORS[1]} 50%)`,
                }}
              >
                <div
                  className={'input-range-track-background'}
                  ref={props.ref}
                  style={{
                    background: getTrackBackground({
                      values: values,
                      colors: TRACK_COLORS,
                      min,
                      max,
                      direction: direction,
                    }),
                  }}
                >
                  {children}
                </div>
              </div>
            )}
            renderThumb={({ props, isDragged }) => (
              <div
                className={`input-range-handle type-${thumbType} ${isDragged ? 'active' : ''}`}
                {...props}
                aria-label={labelText}
                key={props.key}
                type={props.type}
                style={getThumbStyle(props.style, isDragged, thumbType)}
              >
                {renderThumbContent()}
                <div
                  className={
                    'input-range-value ' +
                    (isDragged || values[0].toFixed(1) !== value.toFixed(1)
                      ? 'd-block'
                      : 'd-none') +
                    (horizontal ? '' : ' vertical')
                  }
                  key={`value-${values[0]}`}
                  data-testid="input-range-value"
                  style={{
                    display:
                      isDragged || values[0].toFixed(1) !== value.toFixed(1)
                        ? 'block'
                        : 'none',
                    position: 'absolute',
                    top: horizontal ? '-36px' : '-10px',
                    right: !horizontal && '-42px',
                    color: '#fff',
                    fontWeight: '400',
                    fontSize: '11px',
                    padding: '4px',
                    borderRadius: '4px',
                    backgroundColor: '#000',
                    minWidth: horizontal && '36px',
                    textAlign: 'center',
                  }}
                >
                  {values[0].toFixed(1)}
                </div>
              </div>
            )}
          />
        </div>
        {showInput && (
          <div className="input-range-input">
            <Input
              value={values[0]}
              onChange={({ target: { value } }) => {
                const parsed = parseFloat(value)
                handleOnChange([isNaN(parsed) ? min : parsed])
              }}
              min={min}
              max={max}
              id={id}
              inputRef={inputRef}
            />
          </div>
        )}
      </div>
    </div>
  )
}
