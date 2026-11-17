// Import shared mocks
import 'tests/mocks'

import { act, useState } from 'react'
import { renderWithProviders } from 'tests/testUtils'
import { ToggleButtons } from './ToggleButtons'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

function BasicWrapper() {
  const [value, setValue] = useState(-1)
  return (
    <div data-testid="toggle-buttons-toggle-group">
      <p className="d-none" data-testid="output">
        {JSON.stringify(value)}
      </p>
      <ToggleButtons
        id="toggle-buttons"
        value={value}
        onChange={(v) => setValue(v)}
      />
    </div>
  )
}

function WithThreeOptionsWrapper() {
  const [value, setValue] = useState(-1)
  return (
    <div data-testid="toggle-buttons-toggle-group-three-options">
      <p className="d-none" data-testid="output">
        {JSON.stringify(value)}
      </p>
      <ToggleButtons
        id="toggle-buttons"
        labelText="Toggle Button With Three Options"
        value={value}
        onOffToggle={false}
        onChange={(v) => setValue(v)}
      />
    </div>
  )
}

describe('ToggleButtons', () => {
  describe('Basic (two options)', () => {
    test('Should display exactly two input fields', async () => {
      await renderWithProviders(<BasicWrapper />)
      const buttonsGroup = screen.getByTestId('toggle-buttons-toggle-group')
      const inputs = buttonsGroup.querySelectorAll('input')
      expect(inputs).toHaveLength(2)
    })

    test('Should output true when clicking on the on button', async () => {
      await renderWithProviders(<BasicWrapper />)
      const onButton = screen.getByTestId(
        'toggleButton-toggle-buttons-option-0'
      )
      const output = screen.getByTestId('output')

      await userEvent.click(onButton)
      expect(output).toHaveTextContent('true')
    })

    test('Should output false when clicking on the off button', async () => {
      await renderWithProviders(<BasicWrapper />)
      const offButton = screen.getByTestId(
        'toggleButton-toggle-buttons-option-1'
      )
      const output = screen.getByTestId('output')

      await userEvent.click(offButton)
      expect(output).toHaveTextContent('false')
    })

    test('Should output true when clicking on the on button after clicking on the off button', async () => {
      await renderWithProviders(<BasicWrapper />)
      const onButton = screen.getByTestId(
        'toggleButton-toggle-buttons-option-0'
      )
      const offButton = screen.getByTestId(
        'toggleButton-toggle-buttons-option-1'
      )
      const output = screen.getByTestId('output')

      await userEvent.click(offButton)
      expect(output).toHaveTextContent('false')

      await userEvent.click(onButton)
      expect(output).toHaveTextContent('true')
    })
  })

  describe('With three options', () => {
    test('Should display exactly three input fields', async () => {
      await renderWithProviders(<WithThreeOptionsWrapper />)
      const buttonsGroup = screen.getByTestId(
        'toggle-buttons-toggle-group-three-options'
      )
      const inputs = buttonsGroup.querySelectorAll('input')
      expect(inputs).toHaveLength(3)
    })

    test('Should output "1" when clicking on the first option', async () => {
      await renderWithProviders(<WithThreeOptionsWrapper />)
      const onButton = screen.getByTestId(
        'toggleButton-toggle-buttons-option-0'
      )
      const output = screen.getByTestId('output')

      await userEvent.click(onButton)
      expect(output).toHaveTextContent('"1"')
    })

    test('Should output "0" when clicking on the second option', async () => {
      await renderWithProviders(<WithThreeOptionsWrapper />)
      const offButton = screen.getByTestId(
        'toggleButton-toggle-buttons-option-1'
      )
      const output = screen.getByTestId('output')

      await userEvent.click(offButton)
      expect(output).toHaveTextContent('"0"')
    })
  })
})

// React Query notifies subscribers on a setTimeout(0), so wait a tick for the re-render
const setSurveyUpdatePermission = (value) =>
  act(async () => {
    queryClient.setQueryData(
      ['appState', STATES.HAS_SURVEY_UPDATE_PERMISSION],
      value
    )
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

// Fails when no option is rendered, so the per-option checks can't pass empty
const getOptionInputs = () => {
  const inputs = document.querySelectorAll(
    '#locked-toggle input, #plain-toggle input'
  )
  expect(inputs.length).toBeGreaterThan(0)

  return inputs
}

describe('ToggleButtons disabled states', () => {
  afterEach(async () => {
    await setSurveyUpdatePermission(true)
  })

  test('is disabled when activeDisabled is set', async () => {
    await renderWithProviders(
      <ToggleButtons id="locked-toggle" activeDisabled />
    )

    getOptionInputs().forEach((input) => expect(input).toBeDisabled())
  })

  test('is disabled without survey update permission', async () => {
    await renderWithProviders(
      <ToggleButtons id="locked-toggle" noPermissionDisabled />
    )
    await setSurveyUpdatePermission(false)

    getOptionInputs().forEach((input) => expect(input).toBeDisabled())
  })

  test('stays enabled with survey update permission', async () => {
    await renderWithProviders(
      <ToggleButtons id="locked-toggle" noPermissionDisabled />
    )
    await setSurveyUpdatePermission(true)

    getOptionInputs().forEach((input) => expect(input).toBeEnabled())
  })

  test('renders outside a QueryClientProvider', () => {
    render(<ToggleButtons id="plain-toggle" noPermissionDisabled />)

    getOptionInputs().forEach((input) => expect(input).toBeEnabled())
  })
})
