// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { PrivacyPolicyButtons } from './PrivacyPolicyButtons'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { useState } from 'react'

describe('PrivacyPolicyButtons', () => {
  beforeEach(async () => {
    const PrivacyPolicyButtonsWrapper = () => {
      const [value, setValue] = useState(1)
      return (
        <>
          <p className="d-none" data-testid="output">
            {value}
          </p>
          <PrivacyPolicyButtons
            labelText="Privacy Policy Buttons"
            value={value}
            update={(value) => setValue(value)}
          />
        </>
      )
    }

    await renderWithProviders(<PrivacyPolicyButtonsWrapper />)
  })

  test('Should display the label text', async () => {
    const labelText = screen.getByTestId('privacy-policy-buttons-label-text')
    expect(labelText.innerHTML).toBe('Privacy Policy Buttons')
  })

  test('Should output 1 when clicking on the inline button', async () => {
    const inlineButton = screen.getByTestId('inline-privacy-btn')
    const output = screen.getByTestId('output')

    await userEvent.click(inlineButton)
    expect(output.innerHTML).toBe('1')
  })

  test('Should output 2 when clicking on the pop-up button', async () => {
    const popupButton = screen.getByTestId('popup-privacy-btn')
    const output = screen.getByTestId('output')

    await userEvent.click(popupButton)
    expect(output.innerHTML).toBe('2')
  })

  test('Should output 0 when clicking on the no button', async () => {
    const noButton = screen.getByTestId('no-privacy-btn')
    const output = screen.getByTestId('output')

    await userEvent.click(noButton)
    expect(output.innerHTML).toBe('0')
  })
})
