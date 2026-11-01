// Import shared mocks
import 'tests/mocks'

import { act } from 'react'
import { screen, fireEvent } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { getSiteUrl, STATES } from 'helpers'
import { queryClient } from 'queryClient'
import surveyData from 'helpers/data/survey-detail.json'

import { QuestionGroupHeader } from './QuestionGroupHeader'

describe('QuestionGroupHeader', () => {
  const questionGroup = { gid: 3, l10ns: {} }
  let openSpy

  beforeEach(async () => {
    openSpy = jest.spyOn(window, 'open').mockImplementation(() => {})
    await renderWithProviders(
      <QuestionGroupHeader
        questionGroup={questionGroup}
        duplicateGroup={() => {}}
        deleteGroup={() => {}}
        handleFocusGroup={() => {}}
        setShowQuestions={() => {}}
        showQuestions={false}
        handleUpdate={() => {}}
        language="en"
        onErrors={() => {}}
        isFocused
      />
    )
  })

  afterEach(() => {
    openSpy.mockRestore()
    queryClient.setQueryData(['appState', STATES.IS_SURVEY_ACTIVE], false)
    queryClient.setQueryData(
      ['appState', STATES.HAS_SURVEY_UPDATE_PERMISSION],
      true
    )
  })

  const setSurveyActive = async (value) => {
    await act(async () => {
      queryClient.setQueryData(['appState', STATES.IS_SURVEY_ACTIVE], value)
    })
  }

  test.each([true, false])(
    'Duplicate/Delete disabled state matches IS_SURVEY_ACTIVE = %s',
    async (isSurveyActive) => {
      await setSurveyActive(isSurveyActive)
      fireEvent.click(await screen.findByTestId('meatball-menu-button'))

      const duplicateButton = await screen.findByTestId('duplicate-button')
      const deleteButton = await screen.findByTestId('delete-button')

      if (isSurveyActive) {
        expect(duplicateButton).toHaveClass('disabled')
        expect(deleteButton).toHaveClass('disabled')
      } else {
        expect(duplicateButton).not.toHaveClass('disabled')
        expect(deleteButton).not.toHaveClass('disabled')
      }
    }
  )

  test('Duplicate/Delete are disabled when the user lacks survey update permission', async () => {
    await setSurveyActive(false)
    await act(async () => {
      queryClient.setQueryData(
        ['appState', STATES.HAS_SURVEY_UPDATE_PERMISSION],
        false
      )
    })
    fireEvent.click(await screen.findByTestId('meatball-menu-button'))

    expect(await screen.findByTestId('duplicate-button')).toHaveClass(
      'disabled'
    )
    expect(await screen.findByTestId('delete-button')).toHaveClass('disabled')
  })

  test('Opens the group preview URL in a new tab when the preview icon is clicked', async () => {
    const previewIcon = await screen.findByTestId(
      'question-footer-preview-icon'
    )
    fireEvent.click(previewIcon)

    expect(openSpy).toHaveBeenCalledTimes(1)
    expect(openSpy).toHaveBeenCalledWith(
      getSiteUrl(
        `/index.php/survey/index/action/previewgroup/sid/${surveyData.surveyId}/gid/${questionGroup.gid}/lang/en`
      ),
      '_blank'
    )
  })
})
