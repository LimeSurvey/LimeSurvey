// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { EndScreenSettings } from './EndScreenSettings'
import { screen, waitFor } from '@testing-library/react'

describe('EndScreenSettings', () => {
  test('Should render EndScreenSettings correctly', async () => {
    await renderWithProviders(<EndScreenSettings />)

    const container = await waitFor(() =>
      screen.getByTestId('end-screen-settings')
    )

    expect(container).toBeInTheDocument()
  })
})
