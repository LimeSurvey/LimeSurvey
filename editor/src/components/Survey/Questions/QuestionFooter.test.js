// Import shared mocks
import 'tests/mocks'

import { screen, fireEvent } from '@testing-library/react'

import { renderWithProviders } from 'tests/testUtils'
import { getSiteUrl } from 'helpers'
import { getQuestionTypeInfo } from 'components/QuestionTypes'
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
    const previewIcon = await screen.findByTestId(
      'question-footer-preview-icon'
    )

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

describe('QuestionFooter label sets', () => {
  const renderFooter = (question, isFocused = true) =>
    renderWithProviders(
      <QuestionFooter
        question={{ qid: 42, gid: 7, ...question }}
        handleRemove={() => {}}
        handleDuplicate={() => {}}
        isFocused={isFocused}
      />
    )

  test('Shows the save label set icon for a focused subquestion list question', async () => {
    await renderFooter({
      questionThemeName: getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme,
      subquestions: [{ qid: 1, title: 'SQ001', l10ns: {} }],
    })

    const icon = await screen.findByTestId(
      'question-footer-save-label-set-icon'
    )
    expect(icon.tagName).toBe('BUTTON')
    expect(icon).toHaveAccessibleName('Save subquestions as new label set')
    expect(icon).toBeEnabled()
    fireEvent.click(icon)

    expect(
      await screen.findByTestId('save-label-set-modal')
    ).toBeInTheDocument()
  })

  test('Disables the save label set button when there is nothing to save', async () => {
    await renderFooter({
      questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
      answers: [],
    })

    expect(
      await screen.findByTestId('question-footer-save-label-set-icon')
    ).toBeDisabled()
  })

  test('Does not show the save label set icon for unsupported question types', async () => {
    await renderFooter({
      questionThemeName: getQuestionTypeInfo().SHORT_TEXT.theme,
    })

    expect(
      screen.queryByTestId('question-footer-save-label-set-icon')
    ).not.toBeInTheDocument()
  })

  test('Does not show the save label set icon when the question is not focused', async () => {
    await renderFooter(
      {
        questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
        answers: [{ aid: 1, code: 'A1', l10ns: {} }],
      },
      false
    )

    expect(
      screen.queryByTestId('question-footer-save-label-set-icon')
    ).not.toBeInTheDocument()
  })
})
