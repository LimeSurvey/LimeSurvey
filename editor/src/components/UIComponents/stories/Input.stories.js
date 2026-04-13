import { useRef, useState } from 'react'

import { Input as InputComponent } from '../Input/Input'

export default {
  title: 'UIComponents/Input',
  component: InputComponent,
}

export const Input = () => {
  const [value, setValue] = useState('')

  return (
    <InputComponent
      dataTestId={'story-input-test-id'}
      placeholder="Enter your name"
      labelText={'Name'}
      value={value}
      onChange={({ target: { value } }) => setValue(value)}
    />
  )
}

export function Disabled() {
  return <Input placeholder="Disabled" labelText={'Disabled'} disabled />
}

export function Number() {
  const [value, setValue] = useState('')

  return (
    <Input
      placeholder="Enter your age"
      labelText={'Age'}
      dataTestId={'story-input-test-id'}
      value={value}
      onChange={({ target: { value } }) => setValue(value)}
      onBlur={() => {}}
      type="number"
      max={'120'}
      min={'18'}
      paddinRight="20"
      paddingLeft="50"
    />
  )
}

export const NumericInput = () => {
  const [value, setValue] = useState('3')
  const ref = useRef(3)

  return (
    <InputComponent
      placeholder="Enter your a number of stars"
      labelText={'Numeric Input'}
      dataTestId={'numeric-input'}
      value={value}
      inputRef={ref}
      onChange={({ target: { value } }) => setValue(value)}
      type="number"
    />
  )
}

export const NumericInputWithMinAndMaxValues = () => {
  return (
    <InputComponent
      placeholder="Enter your a number of stars"
      labelText={'Numeric Input'}
      dataTestId={'numeric-input'}
      type="number"
      max={5}
      min={2}
      allowEmpty={true}
    />
  )
}
