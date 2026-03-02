import React from 'react'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { within } from '@storybook/test'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { PublicSurveyAlias } from '../PublicSurveyAlias'
import surveyData from 'helpers/data/survey-detail.json'
import { ACCESS_MODES, ARCHIVE_BASE_TABLE_TOKENS, Toast } from 'helpers'

import {
  getSurveyAccessModeOptions,
  SURVEY_ARCHIVED_TOKENS_QUERY_KEY,
} from './surveyAccessModeHandler'

global.t = (key) => key
jest.mock('helpers', () => {
  const original = jest.requireActual('helpers')
  return {
    ...original,
    Toast: jest.fn(),
  }
})

describe('PublicSurveyAlias - Access Mode Change', () => {
  const mockSurvey = surveyData.survey
  const mockUpdate = jest.fn()
  const mockOnSurveyAccessModeChange = jest.fn()
  const mockSetLink = jest.fn()
  const mockCreateBufferOperation = jest.fn(() => ({
    accessMode: jest.fn(() => ({
      update: jest.fn(),
    })),
  }))
  const mockAddToBuffer = jest.fn()

  beforeEach(() => {
    jest.clearAllMocks()
    queryClient.setQueryData([SURVEY_ARCHIVED_TOKENS_QUERY_KEY], {})
  })

  const testQueryClient = new QueryClient()
  const renderComponent = (props = {}) => {
    return render(
      <QueryClientProvider client={testQueryClient}>
        <PublicSurveyAlias
          survey={mockSurvey}
          update={mockUpdate}
          language={'en'}
          setLink={mockSetLink}
          currentSurveyAccessMode={mockSurvey.access_mode}
          onSurveyAccessModeChange={mockOnSurveyAccessModeChange}
          createBufferOperation={mockCreateBufferOperation}
          addToBuffer={mockAddToBuffer}
          {...props}
        />
      </QueryClientProvider>
    )
  }

  describe('Initial Rendering', () => {
    describe('Open Access Mode', () => {
      beforeEach(() => {
        renderComponent()
      })

      it('should render the dropdown with correct initial selection', () => {
        expect(
          screen.getByRole('button', {
            name: new RegExp(t('Anyone with link'), 'i'),
          })
        ).toBeInTheDocument()
      })

      it('should show the correct description text', () => {
        expect(
          screen.getByText(t('Anyone with the link to this survey can access.'))
        ).toBeInTheDocument()
      })
    })

    describe('Closed Access Mode', () => {
      beforeEach(() => {
        renderComponent({
          survey: {
            ...mockSurvey,
            access_mode: ACCESS_MODES.CLOSED,
          },
          currentSurveyAccessMode: ACCESS_MODES.CLOSED,
        })
      })

      it('should show closed access mode in dropdown', () => {
        expect(screen.getByText(t('Link with access code'))).toBeInTheDocument()
      })

      it('should show the correct description text', () => {
        expect(
          screen.getByText(
            t('Only participants with the link and access code can access.')
          )
        ).toBeInTheDocument()
      })
    })
  })

  describe('Access Mode Change', () => {
    it('should show dropdown options when clicked', async () => {
      renderComponent()

      const dropdownToggle = screen.getByText(t('Anyone with link'))
      fireEvent.click(dropdownToggle)

      await waitFor(() => {
        expect(screen.getByText(t('Link with access code'))).toBeInTheDocument()
      })
    })

    it('should change to closed access mode when selected', async () => {
      renderComponent()

      // Open dropdown
      const dropdownToggle = screen.getByText(t('Anyone with link'))
      fireEvent.click(dropdownToggle)

      // Select closed mode
      const closedOption = await screen.findByText(t('Link with access code'))
      fireEvent.click(closedOption)

      await waitFor(() => {
        expect(mockOnSurveyAccessModeChange).toHaveBeenCalledWith(
          ACCESS_MODES.CLOSED
        )
        expect(mockUpdate).toHaveBeenCalled()
        expect(mockAddToBuffer).toHaveBeenCalled()
      })
    })

    it('should change to open access mode when selected', async () => {
      renderComponent({
        survey: {
          ...mockSurvey,
          access_mode: ACCESS_MODES.CLOSED,
        },
        currentSurveyAccessMode: ACCESS_MODES.CLOSED,
      })

      // Open dropdown
      const dropdownToggle = screen.getByText(t('Link with access code'))
      fireEvent.click(dropdownToggle)

      // Select open mode
      const openOption = await screen.findByText(t('Anyone with link'))
      fireEvent.click(openOption)

      await waitFor(() => {
        expect(mockOnSurveyAccessModeChange).toHaveBeenCalledWith(
          ACCESS_MODES.OPEN_TO_ALL
        )
        expect(mockUpdate).toHaveBeenCalled()
        expect(mockAddToBuffer).toHaveBeenCalled()
      })
    })
  })

  describe('Access Mode Options', () => {
    it('should display all access mode options in dropdown', async () => {
      renderComponent()

      // 1. Find the dropdown toggle button
      const dropdownToggle = screen.getByRole('button', {
        name: new RegExp(
          `${t('Anyone with link')}|${t('Link with access code')}`,
          'i'
        ),
      })

      // 2. Click to open the dropdown
      await userEvent.click(dropdownToggle)

      // 3. Verify dropdown options appear using the actual DOM structure
      await waitFor(() => {
        // Get the dropdown menu div (adjust selector based on your actual rendered HTML)
        const dropdownMenu = document.querySelector('.dropdown-menu.show')
        expect(dropdownMenu).toBeInTheDocument()

        // Get all dropdown items
        const dropdownItems = dropdownMenu.querySelectorAll(
          '.survey-access-mode-dropdown-item'
        )
        expect(dropdownItems.length).toBe(2) // Should have 2 options

        // Verify each option exists
        const options = getSurveyAccessModeOptions()
        Object.values(options).forEach((option, index) => {
          const item = dropdownItems[index]
          expect(item).toHaveTextContent(option.label)
          expect(item).toHaveTextContent(option.description)
        })
      })
    })

    it('should show checkmark next to currently selected option', async () => {
      renderComponent()

      // Open dropdown
      const dropdownToggle = screen.getByRole('button', {
        name: new RegExp(
          `${t('Anyone with link')}|${t('Link with access code')}`,
          'i'
        ),
      })
      await userEvent.click(dropdownToggle)

      // Verify checkmark appears only for selected option
      const options = getSurveyAccessModeOptions()
      const selectedKey =
        mockSurvey.access_mode === ACCESS_MODES.CLOSED
          ? options.closed.key
          : options.open.key

      await waitFor(() => {
        // Get all dropdown menu items
        const menuItems = screen.getAllByRole('menuitem')

        Object.entries(options).forEach(([key, option], index) => {
          const optionElement = menuItems[index]

          // Verify the option contains both label and description
          expect(
            within(optionElement).getByText(option.label)
          ).toBeInTheDocument()
          expect(
            within(optionElement).getByText(option.description)
          ).toBeInTheDocument()

          // Check for checkmark
          const checkmark = within(optionElement).queryByTestId('check-icon')
          if (key === selectedKey) {
            expect(checkmark).toBeInTheDocument()
          } else {
            expect(checkmark).toBeNull()
          }
        })
      })
    })
  })

  describe('Toast Messages on Access Mode Change', () => {
    const clickToChangeAccessMode = async (getByTestId, optionText) => {
      fireEvent.click(getByTestId('access-mode-toggle'))

      const openOption = await screen.findByRole('menuitem', {
        name: new RegExp(optionText, 'i'),
      })
      fireEvent.click(openOption)
    }

    const assertToastCalledWithTestId = (testId) => {
      const toastCall = Toast.mock.calls[0][0]
      expect(toastCall.message.props['data-testid']).toBe(testId)
    }

    describe('(open => closed)', () => {
      it('should not show toast if there is an active table', async () => {
        const { getByTestId } = renderComponent({
          survey: {
            ...mockSurvey,
            access_mode: ACCESS_MODES.CLOSED,
            hasTokens: true,
          },
          currentSurveyAccessMode: ACCESS_MODES.OPEN_TO_ALL,
        })

        await clickToChangeAccessMode(getByTestId, t('Link with access code'))

        await waitFor(() => {
          expect(Toast).not.toHaveBeenCalled()
        })
      })

      it('should not show toast if no active or archived table', async () => {
        queryClient.setQueryData([SURVEY_ARCHIVED_TOKENS_QUERY_KEY], {})

        const { getByTestId } = renderComponent({
          survey: {
            ...mockSurvey,
            access_mode: ACCESS_MODES.CLOSED,
            hasTokens: false,
          },
          currentSurveyAccessMode: ACCESS_MODES.OPEN_TO_ALL,
        })

        await clickToChangeAccessMode(getByTestId, t('Link with access code'))

        await waitFor(() => {
          expect(Toast).not.toHaveBeenCalled()
        })
      })

      it('should show toast if no active table but there are archived tables', async () => {
        queryClient.setQueryData([SURVEY_ARCHIVED_TOKENS_QUERY_KEY], {
          0: {
            types: [ARCHIVE_BASE_TABLE_TOKENS],
            count: 5,
            timestamp: Date.now(),
            hastokens: false,
          },
        })

        const { getByTestId } = renderComponent({
          survey: {
            ...mockSurvey,
            access_mode: ACCESS_MODES.CLOSED,
            hasTokens: false,
          },
          currentSurveyAccessMode: ACCESS_MODES.OPEN_TO_ALL,
        })

        await clickToChangeAccessMode(getByTestId, t('Link with access code'))

        await waitFor(() => {
          expect(Toast).toHaveBeenCalledWith(
            expect.objectContaining({
              'message': expect.anything(),
              'data-testid': 'toast-access-mode-changed',
            })
          )
        })

        assertToastCalledWithTestId('toast-no-participants-closed-mode')
      })
    })

    describe('(closed => open)', () => {
      it('should not show toast if participant table is empty', async () => {
        queryClient.setQueryData([SURVEY_ARCHIVED_TOKENS_QUERY_KEY], {
          0: {
            types: [],
            count: 0,
            timestamp: 0,
            hastokens: false,
          },
        })

        const { getByTestId } = renderComponent({
          survey: {
            ...mockSurvey,
            access_mode: ACCESS_MODES.OPEN_TO_ALL,
            hasTokens: true,
          },
          currentSurveyAccessMode: ACCESS_MODES.CLOSED,
        })

        fireEvent.click(getByTestId('access-mode-toggle'))
        await clickToChangeAccessMode(getByTestId, t('Anyone with link'))

        await waitFor(() => {
          expect(Toast).not.toHaveBeenCalled()
        })
      })

      it('should show toast if participant table is not empty', async () => {
        queryClient.setQueryData([SURVEY_ARCHIVED_TOKENS_QUERY_KEY], {
          0: {
            types: [],
            count: 0,
            timestamp: 0,
            hastokens: true,
          },
        })

        const { getByTestId } = renderComponent({
          survey: {
            ...mockSurvey,
            access_mode: ACCESS_MODES.OPEN_TO_ALL,
            hasTokens: true,
          },
          currentSurveyAccessMode: ACCESS_MODES.CLOSED,
        })

        fireEvent.click(getByTestId('access-mode-toggle'))
        await clickToChangeAccessMode(getByTestId, t('Anyone with link'))

        await waitFor(() => {
          expect(Toast).toHaveBeenCalledWith(
            expect.objectContaining({
              'message': expect.anything(),
              'data-testid': 'toast-access-mode-changed',
            })
          )
        })

        assertToastCalledWithTestId('toast-open-mode-with-participants')
      })
    })
  })
})
