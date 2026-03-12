import { useRef, useState } from 'react'
import { userEvent, within, waitFor } from '@storybook/test'
import { expect } from '@storybook/test'

import { Input as InputComponent } from '../Input/Input'
import { sleep } from 'helpers/sleep'

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

Input.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('story-input-test-id'))
  const input = getByTestId('story-input-test-id')

  await step('Should have the value "random text 123"', async () => {
    await userEvent.type(input, 'random text 123', { delay: 60 })
    await expect(input.value).toBe('random text 123')
    await sleep()
  })

  await step('Should be able to clear the text', async () => {
    await userEvent.clear(input)
    await expect(input.value).toBe('')
    await sleep()
  })
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

Number.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('story-input-test-id'))

  const input = getByTestId('story-input-test-id')

  await step('Should have the value "64"', async () => {
    await userEvent.type(input, '64', { delay: 120 })
    await expect(input.value).toBe('64')
    await sleep()
  })

  await step('Should be able to clear the text', async () => {
    await userEvent.clear(input)
    await expect(input.value).toBe('')
    await sleep()
  })
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

async function type(input, value) {
  await userEvent.type(input, value)
}

NumericInputWithMinAndMaxValues.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('numeric-input'))
  const input = getByTestId('numeric-input')

  userEvent.clear(input)

  await step('Should be able to rest', async () => {
    userEvent.clear(input)
    await expect(input.value).toBe('')
    await sleep()
    userEvent.tab()
    await expect(input.value).toBe('')
    await sleep()
  })

  await step(
    'Should have the min value if you entered a number less than min',
    async () => {
      userEvent.clear(input)
      await type(input, '1', { delay: 60 })
      await expect(input.value).toBe('2')
    }
  )

  await step('Should have the numbers "4" after typing "r4ndom"', async () => {
    userEvent.clear(input)
    await type(input, 'r4ndom', '4')
    await expect(input.value).toBe('4')
  })
}
