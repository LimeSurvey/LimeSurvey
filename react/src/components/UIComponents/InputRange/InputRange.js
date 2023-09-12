import React, { useEffect, useState } from 'react'
import { Direction, Range, getTrackBackground } from 'react-range'
import { Form } from 'react-bootstrap'
import { Input } from '..'

export const InputRange = ({
  labelText,
  min = 0,
  max = 100,
  step = 1,
  value = 0,
  onChange = () => {},
  showInput = true,
  direction,
}) => {
  const [values, setValues] = useState([value])
  const [callOnChange, setCallOnChange] = useState(false)
  const [horizontal, setHorizontal] = useState(direction === Direction.Right)
  const inputRef = React.useRef(null)

  const handleOnChange = (values) => {
    setValues([...values])
  }

  useEffect(() => {
    setCallOnChange(false)
    setTimeout(() => {
      if (callOnChange) {
        onChange(values)
      }
      setCallOnChange(true)
    }, 0)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [values[0]])

  useEffect(() => {
    setHorizontal(direction === Direction.Right)
  }, [direction])

  return (
    <>
      {labelText && <Form.Label>{labelText}</Form.Label>}
      <div className="d-flex align-items-center w-100">
        <div className="me-2 w-100">
          <Range
            values={values}
            step={step}
            min={min}
            max={max}
            direction={direction}
            onChange={handleOnChange}
            renderTrack={({ props, children }) => (
              <div
                onMouseDown={props.onMouseDown}
                onTouchStart={props.onTouchStart}
                style={{
                  ...props.style,
                  height: horizontal ? '4px' : '200px',
                  display: 'flex',
                  width: horizontal ? '100%' : '4px',
                }}
              >
                <div
                  ref={props.ref}
                  style={{
                    height: horizontal ? '4px' : '100%',
                    width: horizontal ? '100%' : '4px',
                    borderRadius: '2px',
                    background: getTrackBackground({
                      values: values,
                      colors: ['#8146F6', '#DDDEE8'],
                      min: min,
                      max: max,
                      direction: direction,
                    }),
                    alignSelf: 'center',
                  }}
                >
                  {children}
                </div>
              </div>
            )}
            renderThumb={({ props, isDragged }) => (
              <div
                {...props}
                style={{
                  ...props.style,
                  height: '12px',
                  width: '12px',
                  borderRadius: '50%',
                  backgroundColor: 'rgb(129, 70, 246)',
                  display: 'flex',
                  justifyContent: 'center',
                  alignItems: 'center',
                  boxShadow: isDragged
                    ? '0 0 0 1.5px #fff, 0 0 0 .3rem rgba(129, 70, 246, 0.25)'
                    : 'none',
                }}
              >
                <div
                  style={{
                    display:
                      isDragged || values[0].toFixed(1) !== '0.0'
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
          <div style={{ width: '125px' }}>
            <Input
              value={values[0]}
              onChange={({ target: { value } }) =>
                handleOnChange([parseFloat(value) || min])
              }
              type="number"
              min={min}
              max={max}
              inputRef={inputRef}
            />
          </div>
        )}
      </div>
    </>
  )
}
