// Import shared mocks
import 'tests/mocks'

import { screen, fireEvent } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { getSiteUrl } from 'helpers'
import surveyData from 'helpers/data/survey-detail.json'

import { QuestionFooter } from './QuestionFooter'

describe('QuestionFooter', () => {
  const question = { qid: 42, gid: 7 }
  let openSpy

  beforeEach(async () => {
    openSpy = jest.spyOn(window, 'open').mockImplementation(() => {})
    await renderWithProviders(
      <QuestionFooter
        question={question}
        handleRemove={() => {}}
        handleDuplicate={() => {}}
        isFocused
      />
    )
  })

  afterEach(() => {
    openSpy.mockRestore()
  })

  test('Opens the question preview URL in a new tab when the preview icon is clicked', async () => {
    const previewIcon = (
      await screen.findAllByTestId('question-footer-copy-icon')
    )[0]
    fireEvent.click(previewIcon)

    expect(openSpy).toHaveBeenCalledTimes(1)
    const [url, target] = openSpy.mock.calls[0]
    expect(target).toBe('_blank')
    expect(url).toContain(
      getSiteUrl(
        `/index.php/survey/index/action/previewquestion/sid/${surveyData.surveyId}/gid/${question.gid}/qid/${question.qid}/lang/`
      )
    )
  })
})
