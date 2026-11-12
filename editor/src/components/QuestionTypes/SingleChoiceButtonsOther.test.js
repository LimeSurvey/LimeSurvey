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

    const otherInput = await screen.findByTestId('other-option-input')

    expect(otherInput.closest('[data-testid="child-option"]')).toBeNull()
    expect(otherInput.closest('.single-choice-buttons-other')).not.toBeNull()
    expect(screen.getAllByTestId('other-option-input')).toHaveLength(1)
  })

  test('multiple choice buttons theme renders it below the button grid', async () => {
    await renderWithProviders(
      <OptionQuestionViewMode
        question={{
          qid: 1,
          gid: 1,
          mandatory: false,
          attributes: {},
          other: 'Y',
          questionThemeName:
            getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme,
        }}
        language="en"
        _children={[
          { qid: 11, title: 'SQ001', l10ns: { en: { question: 'Sub 1' } } },
          { qid: 12, title: 'SQ002', l10ns: { en: { question: 'Sub 2' } } },
        ]}
        participantMode={true}
        surveySettings={{ showNoAnswer: false }}
        values={[
          { key: 'fieldSQ001', qid: 11, value: '' },
          { key: 'fieldSQ002', qid: 12, value: '' },
          {
            key: 'field_Cother',
            otherText: { key: 'field_Cother', value: 'Typed text' },
            otherChecked: { key: 'field_Cotherchecked', value: 'Y' },
          },
        ]}
      />
    )

    const otherInput = await screen.findByTestId('other-option-input')

    expect(otherInput.closest('[data-testid="child-option"]')).toBeNull()
    expect(otherInput.closest('.single-choice-buttons-other')).not.toBeNull()
    expect(otherInput.value).toBe('Typed text')
    expect(screen.getAllByTestId('other-option-input')).toHaveLength(1)
    expect(
      document.querySelector('.children-parent.single-choice-buttons')
    ).not.toBeNull()
  })

  test('list radio theme keeps it next to the "Other" option', async () => {
    await renderWithOther(getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme)

    const otherInput = await screen.findByTestId('other-option-input')

    expect(otherInput.closest('[data-testid="child-option"]')).not.toBeNull()
    expect(document.querySelector('.single-choice-buttons-other')).toBeNull()
  })
})
