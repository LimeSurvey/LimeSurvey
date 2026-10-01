// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { LeftSideBar } from './LeftSideBar'
import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { getSurveyPanels } from 'helpers/options'

describe('LeftSideBar', () => {
  beforeEach(async () => {
    await renderWithProviders(<LeftSideBar />)
    await waitFor(() => screen.getByTestId('left-sidebar'))
  })

  test('Should be able to toggle the structure panel', async () => {
    const closeButton = screen.queryByTestId('btn-close-structure')

    if (closeButton) {
      await userEvent.click(closeButton)
      expect(
        screen.queryByTestId('editor-structure-panel')
      ).not.toBeInTheDocument()

      await userEvent.click(
        screen.getByTestId(`btn-${getSurveyPanels().structure.panel}-open`)
      )

      expect(
        await screen.findByTestId('editor-structure-panel')
      ).toBeInTheDocument()
    } else {
      await userEvent.click(
        screen.getByTestId(`btn-${getSurveyPanels().structure.panel}-open`)
      )

      expect(
        await screen.findByTestId('editor-structure-panel')
      ).toBeInTheDocument()

      await userEvent.click(screen.queryByTestId('btn-close-structure'))
      expect(
        screen.queryByTestId('editor-structure-panel')
      ).not.toBeInTheDocument()
    }
  })
})
