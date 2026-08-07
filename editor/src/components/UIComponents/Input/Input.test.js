// Import shared mocks
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import 'tests/mocks'
import { renderWithProviders } from 'tests/testUtils'

import { Input } from './Input'

describe('Input', () => {
  let input

  beforeEach(async () => {
    await renderWithProviders(
      <Input
        dataTestId={'input-test'}
        placeholder="Enter your name"
        labelText={'Name'}
        onChange={() => {}}
      />
    )

    input = screen.getByTestId('input-test')
  })

  afterEach(() => {
    input.remove()
  })

  test('Should have the value "random text 123"', async () => {
    await userEvent.type(input, 'random text 123')
    expect(input.value).toBe('random text 123')
  })

  test('Should be able to clear the text', async () => {
    await userEvent.type(input, 'random text')
    await userEvent.clear(input)
    expect(input.value).toBe('')
  })
})

describe('Input Number', () => {
  let input

  beforeEach(async () => {
    await renderWithProviders(
      <Input
        dataTestId={'numeric-input'}
        placeholder="Enter your name"
        labelText={'Name'}
        type="number"
        onChange={() => {}}
      />
    )

    input = screen.getByTestId('numeric-input')
  })

  afterEach(() => {
    input.remove()
  })

  test('Should have the value "64"', async () => {
    await userEvent.type(input, '64')
    expect(input.value).toBe('64')
  })

  test('Should be able to clear the text', async () => {
    await userEvent.type(input, '64')
    await userEvent.clear(input)
    expect(input.value).toBe('')
  })
})

describe('NumericInputWithMinAndMaxValues', () => {
  let input

  beforeEach(async () => {
    await renderWithProviders(
      <Input
        placeholder="Enter a number of stars"
        labelText={'Numeric Input'}
        dataTestId={'numeric-input'}
        type="number"
        max={5}
        min={2}
        allowEmpty={true}
      />
    )

    input = screen.getByTestId('numeric-input')
  })

  afterEach(() => {
    input.remove()
  })

  test('Should have the min value if you entered a number less than min', async () => {
    await userEvent.type(input, '1')
    expect(input.value).toBe('2')
  })

  test('Should have the numbers "4" after typing "r4ndom"', async () => {
    await userEvent.type(input, 'random4')
    expect(input.value).toBe('4')
  })
})
