// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { Range } from './Range'
import { screen } from '@testing-library/react'

describe('Range', () => {
  test('Should render Range correctly', async () => {
    await renderWithProviders(<Range />)

    const container = screen.getByTestId('range')
    const slider = screen.getByRole('slider')

    expect(container).toBeInTheDocument()
    expect(slider).toBeInTheDocument()
  })
})
