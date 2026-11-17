// Import shared mocks
import { act } from 'react'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import 'tests/mocks'
import { renderWithProviders } from 'tests/testUtils'

import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

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

describe('Input with defaultValue', () => {
  test('Should display defaultValue when value is omitted', async () => {
    await renderWithProviders(
      <Input
        dataTestId={'default-value-input'}
        defaultValue="foo"
        onChange={() => {}}
      />
    )

    const input = screen.getByTestId('default-value-input')
    expect(input.value).toBe('foo')
    input.remove()
  })
})

// React Query notifies subscribers on a setTimeout(0), so wait a tick for the re-render
const setSurveyActive = (value) =>
  act(async () => {
    queryClient.setQueryData(['appState', STATES.IS_SURVEY_ACTIVE], value)
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

describe('Input with activeDisabled', () => {
  afterEach(async () => {
    await setSurveyActive(false)
  })

  test('Should be disabled when the survey is active', async () => {
    await renderWithProviders(
      <Input dataTestId={'active-disabled-input'} activeDisabled={true} />
    )
    await setSurveyActive(true)

    expect(screen.getByTestId('active-disabled-input')).toBeDisabled()
  })

  test('Should stay enabled when the survey is not active', async () => {
    await renderWithProviders(
      <Input dataTestId={'active-disabled-input'} activeDisabled={true} />
    )
    await setSurveyActive(false)

    expect(screen.getByTestId('active-disabled-input')).toBeEnabled()
  })

  test('Should stay enabled on an active survey without activeDisabled', async () => {
    await renderWithProviders(<Input dataTestId={'plain-input'} />)
    await setSurveyActive(true)

    expect(screen.getByTestId('plain-input')).toBeEnabled()
  })

  test('Should render outside a QueryClientProvider', () => {
    render(<Input dataTestId={'no-provider-input'} activeDisabled={true} />)

    expect(screen.getByTestId('no-provider-input')).toBeEnabled()
  })
})
