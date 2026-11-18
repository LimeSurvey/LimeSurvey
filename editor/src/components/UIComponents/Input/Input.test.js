// Import shared mocks
import { useState } from 'react'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import 'tests/mocks'
import { renderWithProviders, setSurveyUpdatePermission } from 'tests/testUtils'

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

describe('Input with activeDisabled', () => {
  test('Should be disabled when activeDisabled is set', async () => {
    await renderWithProviders(
      <Input dataTestId={'active-disabled-input'} activeDisabled={true} />
    )

    expect(screen.getByTestId('active-disabled-input')).toBeDisabled()
  })

  test('Should stay enabled without activeDisabled', async () => {
    await renderWithProviders(<Input dataTestId={'plain-input'} />)

    expect(screen.getByTestId('plain-input')).toBeEnabled()
  })

  test('Should render outside a QueryClientProvider', () => {
    render(
      <>
        <Input dataTestId={'no-provider-locked'} activeDisabled={true} />
        <Input dataTestId={'no-provider-input'} noPermissionDisabled={true} />
      </>
    )

    expect(screen.getByTestId('no-provider-locked')).toBeDisabled()
    expect(screen.getByTestId('no-provider-input')).toBeEnabled()
  })
})

describe('Input with noPermissionDisabled', () => {
  afterEach(async () => {
    await setSurveyUpdatePermission(true)
  })

  test('Should be disabled without survey update permission', async () => {
    await renderWithProviders(
      <Input dataTestId={'permission-input'} noPermissionDisabled={true} />
    )
    await setSurveyUpdatePermission(false)

    expect(screen.getByTestId('permission-input')).toBeDisabled()
  })

  test('Should stay enabled with survey update permission', async () => {
    await renderWithProviders(
      <Input dataTestId={'permission-input'} noPermissionDisabled={true} />
    )
    await setSurveyUpdatePermission(true)

    expect(screen.getByTestId('permission-input')).toBeEnabled()
  })

  test('Should stay enabled without permission when noPermissionDisabled is not set', async () => {
    await renderWithProviders(<Input dataTestId={'plain-input'} />)
    await setSurveyUpdatePermission(false)

    expect(screen.getByTestId('plain-input')).toBeEnabled()
  })
})

describe('Input when noPermissionDisabled changes', () => {
  const ToggleablePermissionInput = () => {
    const [noPermissionDisabled, setNoPermissionDisabled] = useState(false)
    return (
      <>
        <Input
          dataTestId={'toggled-input'}
          noPermissionDisabled={noPermissionDisabled}
        />
        <button onClick={() => setNoPermissionDisabled(true)}>lock</button>
      </>
    )
  }

  test('Should keep the same field and its typed value', async () => {
    await renderWithProviders(<ToggleablePermissionInput />)
    const input = screen.getByTestId('toggled-input')
    await userEvent.type(input, 'typed')

    await userEvent.click(screen.getByRole('button', { name: 'lock' }))

    expect(screen.getByTestId('toggled-input')).toBe(input)
    expect(input).toHaveValue('typed')
  })
})
