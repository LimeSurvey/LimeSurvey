// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { AlignButtons } from './AlignButtons'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { useState } from 'react'

describe('AlignButtons', () => {
  beforeEach(async () => {
    const AlignButtonsWrapper = () => {
      const [value, setValue] = useState('right')
      return (
        <>
          <p className="d-none" data-testid="output">
            {value}
          </p>
          <AlignButtons
            labelText="Aligned Buttons"
            value={value}
            update={(value) => setValue(value)}
          />
        </>
      )
    }

    await renderWithProviders(<AlignButtonsWrapper />)
  })

  test('Should display the label text', async () => {
    const labelText = screen.getByTestId('align-buttons-label-text')
    expect(labelText.innerHTML).toBe('Aligned Buttons')
  })

  test('Should output left when clicking on the left button', async () => {
    const leftButton = screen.getByTestId('left-align-btn')
    const output = screen.getByTestId('output')

    await userEvent.click(leftButton)
    expect(output.innerHTML).toBe('left')
  })

  test('Should output center when clicking on the center button', async () => {
    const centerButton = screen.getByTestId('center-align-btn')
    const output = screen.getByTestId('output')

    await userEvent.click(centerButton)
    expect(output.innerHTML).toBe('center')
  })

  test('Should output right when clicking on the right button', async () => {
    const rightButton = screen.getByTestId('right-align-btn')
    const output = screen.getByTestId('output')

    await userEvent.click(rightButton)
    expect(output.innerHTML).toBe('right')
  })
})
