import React, { useEffect } from 'react'
import { Form } from 'react-bootstrap'

const NumericInput = ({
  value = '',
  dataTestId = '',
  onChange = () => {},
  inputRef = { current: {} },
  placeholder = '',
  id = '',
  max = 100,
  min = 0,
  allowEmpty = false,
}) => {
  const handleOnChange = (event) => {
    const value = event.target.value

    if (+value > max) {
      event.target.value = max
    } else if (+value < min) {
      event.target.value = min
    } else {
      event.target.value = value
    }

    onChange(event)
  }

  const handleOnBlur = (event) => {
    const value = event.target.value

    if (!+value && !allowEmpty) {
      inputRef.current.value = 0
      event.target.value = 0
    } else if (+value < min) {
      inputRef.current.value = min
      event.target.value = min
    } else if (+value > max) {
      inputRef.current.value = max
      event.target.value = max
    }

    onChange(event)
  }

  useEffect(() => {
    if (!inputRef?.current) {
      return
    }

    inputRef.current.value = value
  }, [inputRef, value])

  return (
    <div className="numeric">
      <Form.Control
        type="number"
        ref={inputRef}
        id={id}
        data-testid={dataTestId}
        placeholder={placeholder}
        onChange={handleOnChange}
        onBlur={handleOnBlur}
        defaultValue={value}
        onKeyDown={(event) => {
          if (['e', 'E'].includes(event.key)) {
            event.preventDefault()
          }
        }}
        className="form-control"
      />
      {/* <div className="numeric-wrapper">
        <div className="numeric-button numeric-up" onClick={handleIncrement}>
          <ArrowUpIcon className="up-icon" />
        </div>
        <div className="numeric-button numeric-down" onClick={handleDecrement}>
          <ArrowDownIcon className="down-icon" />
        </div>
      </div> */}
    </div>
  )
}
export default NumericInput
