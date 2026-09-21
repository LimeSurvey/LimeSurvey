// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { TopBar } from './TopBar'
import { act, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { PAGES, STATES, URLS } from 'helpers'
import { queryClient } from 'queryClient'
import surveyData from 'helpers/data/survey-detail.json'

jest.mock('components/PublishSettings/SurveyActivationHandler', () => {
  const React = require('react')

  return React.forwardRef(function MockSurveyActivationHandler(
    { showOverViewModal, setShowOverViewModal },
    ref
  ) {
    void ref
    return (
      <button
        data-testid="overview-modal-state"
        data-open={showOverViewModal}
        onClick={() => setShowOverViewModal(false)}
      />
    )
  })
})

describe('TopBar', () => {
  let Survey

  beforeEach(() => {
    Survey = surveyData.survey
  })

  test('Logo should links back to the dashboard of core app', async () => {
    await renderWithProviders(<TopBar surveyId={Survey.sid} />)

    await waitFor(() => screen.getByTestId('add-question-button'))
    await waitFor(async () =>
      expect(screen.getByTestId('add-question-button')).not.toHaveAttribute(
        'disabled'
      )
    )

    const logoATag = screen.getByTestId('logo-a-tag')
    expect(logoATag).toHaveAttribute('href', `${URLS.ADMIN}`)
  })

  test('+/x buttons should toggle the question type selector', async () => {
    await renderWithProviders(<TopBar surveyId={Survey.sid} />)

    await waitFor(() => screen.getByTestId('add-question-button'))
    await waitFor(async () =>
      expect(screen.getByTestId('add-question-button')).not.toHaveAttribute(
        'disabled'
      )
    )

    const addQuestionButton = screen.getByTestId('add-question-button')
    expect(addQuestionButton).toBeInTheDocument()

    await userEvent.click(addQuestionButton)

    let questionTypeSelector = screen.queryByTestId('topbar-question-inserter')

    if (questionTypeSelector) {
      // If selector is visible, clicking the button should hide it
      await userEvent.click(addQuestionButton)
      questionTypeSelector = screen.queryByTestId('topbar-question-inserter')
      expect(questionTypeSelector).not.toBeInTheDocument()

      // then testing if we can show it again
      await userEvent.click(addQuestionButton)
      questionTypeSelector = screen.queryByTestId('topbar-question-inserter')
      expect(questionTypeSelector).toBeInTheDocument()
    } else {
      // If selector is not visible, clicking the button should show it
      await userEvent.click(addQuestionButton)
      questionTypeSelector = screen.queryByTestId('topbar-question-inserter')
      expect(questionTypeSelector).toBeInTheDocument()

      // then testing if we can hide it again
      await userEvent.click(addQuestionButton)
      questionTypeSelector = screen.queryByTestId('topbar-question-inserter')
      expect(questionTypeSelector).not.toBeInTheDocument()
    }
  })

  test('Survey title should exist', async () => {
    await renderWithProviders(<TopBar surveyId={Survey.sid} />)

    await waitFor(() => screen.getByTestId('add-question-button'))
    await waitFor(async () =>
      expect(screen.getByTestId('add-question-button')).not.toHaveAttribute(
        'disabled'
      )
    )

    const surveyTitleContentEditor = screen.getByTestId(
      'topbar-survey-title-content-editor'
    )

    await waitFor(async () =>
      expect(surveyTitleContentEditor.innerHTML).toBe(
        Survey.languageSettings.en.title
      )
    )
  })

  test('Publish settings should exist', async () => {
    await renderWithProviders(<TopBar surveyId={Survey.sid} />)

    await waitFor(() => screen.getByTestId('add-question-button'))
    await waitFor(async () =>
      expect(screen.getByTestId('add-question-button')).not.toHaveAttribute(
        'disabled'
      )
    )

    const publishSettings = screen.getByTestId('publish-settings')
    expect(publishSettings).toBeInTheDocument()
  })

  test('Overview closes on navigation and does not reopen when returning to the editor', async () => {
    queryClient.clear()
    Survey.active = true
    queryClient.setQueryData(['appState', STATES.TOPBAR_CONFIG], {
      pageName: PAGES.EDITOR,
      shouldAutoOpenOverview: true,
    })
    queryClient.setQueryData(
      ['appState', STATES.LOADED_SURVEY_ID],
      Survey.sid
    )

    await renderWithProviders(<TopBar surveyId={Survey.sid} />)

    const overviewModalState = await screen.findByTestId('overview-modal-state')
    await waitFor(() =>
      expect(overviewModalState).toHaveAttribute('data-open', 'true')
    )

    act(() => {
      queryClient.setQueryData(['appState', STATES.TOPBAR_CONFIG], {
        pageName: PAGES.RESPONSES,
        shouldAutoOpenOverview: false,
      })
    })
    await waitFor(() =>
      expect(overviewModalState).toHaveAttribute('data-open', 'false')
    )

    act(() => {
      queryClient.setQueryData(['appState', STATES.TOPBAR_CONFIG], {
        pageName: PAGES.EDITOR,
        shouldAutoOpenOverview: true,
      })
    })

    await waitFor(() =>
      expect(overviewModalState).toHaveAttribute('data-open', 'false')
    )
  })

  test('Overview does not open for an inactive survey', async () => {
    queryClient.clear()
    Survey.active = false
    queryClient.setQueryData(['appState', STATES.TOPBAR_CONFIG], {
      pageName: PAGES.EDITOR,
      shouldAutoOpenOverview: true,
    })
    queryClient.setQueryData(
      ['appState', STATES.LOADED_SURVEY_ID],
      Survey.sid
    )

    await renderWithProviders(<TopBar surveyId={Survey.sid} />)

    const overviewModalState = await screen.findByTestId('overview-modal-state')
    expect(overviewModalState).toHaveAttribute('data-open', 'false')
  })

  test('Overview does not open from a stale active survey in the cache', async () => {
    queryClient.clear()
    Survey.active = true
    queryClient.setQueryData(['appState', STATES.TOPBAR_CONFIG], {
      pageName: PAGES.EDITOR,
      shouldAutoOpenOverview: true,
    })

    await renderWithProviders(<TopBar surveyId={Number(Survey.sid) + 1} />)

    const overviewModalState = await screen.findByTestId('overview-modal-state')
    expect(overviewModalState).toHaveAttribute('data-open', 'false')
  })

  test('Overview does not open before the current survey request completes', async () => {
    queryClient.clear()
    Survey.active = true
    queryClient.setQueryData([STATES.SURVEY], { survey: Survey })
    queryClient.setQueryData(['appState', STATES.TOPBAR_CONFIG], {
      pageName: PAGES.EDITOR,
      shouldAutoOpenOverview: true,
    })

    await renderWithProviders(<TopBar surveyId={Survey.sid} />)

    const overviewModalState = await screen.findByTestId('overview-modal-state')
    expect(overviewModalState).toHaveAttribute('data-open', 'false')
  })
})
