import 'tests/mocks'

import { screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'

import { OptionQuestionViewMode } from './QuestionModes/OptionQuestionViewMode'
import { getQuestionTypeInfo } from './getQuestionTypeInfo'

const renderWithOther = (questionThemeName) =>
  renderWithProviders(
    <OptionQuestionViewMode
      question={{
        qid: 1,
        gid: 1,
        mandatory: false,
        attributes: {},
        other: 'Y',
        questionThemeName,
      }}
      language="en"
      _children={[
        { aid: 1, code: 'A1', l10ns: { en: { answer: 'Answer 1' } } },
        { aid: 2, code: 'A2', l10ns: { en: { answer: 'Answer 2' } } },
      ]}
      participantMode={true}
      surveySettings={{ showNoAnswer: false }}
      values={[
        {
          key: 'field',
          aid: 1,
          value: 'A1',
          otherText: { key: 'fieldother', value: '' },
        },
      ]}
    />
  )

describe('Single choice "Other" text input', () => {
  test('buttons theme renders it below the button grid, outside the grid cells', async () => {
    await renderWithOther(getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme)

    const otherInput = screen.getByTestId('other-option-input')

    expect(otherInput.closest('[data-testid="child-option"]')).toBeNull()
    expect(otherInput.closest('.single-choice-buttons-other')).not.toBeNull()
    expect(screen.getAllByTestId('other-option-input')).toHaveLength(1)
  })

  test('list radio theme keeps it next to the "Other" option', async () => {
    await renderWithOther(getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme)

    const otherInput = screen.getByTestId('other-option-input')

    expect(otherInput.closest('[data-testid="child-option"]')).not.toBeNull()
    expect(document.querySelector('.single-choice-buttons-other')).toBeNull()
  })
})
