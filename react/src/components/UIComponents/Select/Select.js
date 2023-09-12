import React from 'react'
import { Form } from 'react-bootstrap'
import { RandomNumber } from 'helpers'

export const Select = ({
  labelText,
  options,
  selectedOption = options[0],
  onChange,
  size = '',
  className = '',
  style = {},
}) => {
  return (
    <div className={className}>
      {labelText && <Form.Label>{labelText}</Form.Label>}
      <Form.Select
        size={size}
        defaultValue={selectedOption?.value}
        onChange={onChange}
        className={className}
        style={{ ...style }}
      >
        {options.map((option, index) => (
          <option
            key={`${option.value}-${option.label}-${index}-${RandomNumber()}`}
            value={option.value}
          >
            {option.label}
          </option>
        ))}
      </Form.Select>
    </div>
  )
}
