// Import shared mocks
import 'tests/mocks'

import { screen, fireEvent } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { getSiteUrl } from 'helpers'
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
  })

  test('Opens the group preview URL in a new tab when the preview icon is clicked', async () => {
    const previewIcon = await screen.findByTestId('question-footer-copy-icon')
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
