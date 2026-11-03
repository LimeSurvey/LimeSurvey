// Import shared mocks
import 'tests/mocks'

import { useState } from 'react'
import { renderWithProviders } from 'tests/testUtils'
import { ToggleButtons } from './ToggleButtons'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

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
