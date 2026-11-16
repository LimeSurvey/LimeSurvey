import 'tests/mocks'

import { act } from 'react'
import { fireEvent, screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'
import { setSurveyActive, WithCodesShown } from 'tests/surveyEditing'

import { OptionQuestionEditMode } from './OptionQuestionEditMode'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'

const answers = [
  { aid: 1, code: 'A1', l10ns: { en: { answer: 'Answer 1' } } },
  { aid: 2, code: 'A2', l10ns: { en: { answer: 'Answer 2' } } },
  { aid: 3, code: 'A3', l10ns: { en: { answer: 'Answer 3' } } },
]

const subquestions = [
  { qid: 11, title: 'SQ001', l10ns: { en: { question: 'Subquestion 1' } } },
  { qid: 12, title: 'SQ002', l10ns: { en: { question: 'Subquestion 2' } } },
]

const renderEditMode = async ({ questionThemeName, children, handlers }) => {
  await renderWithProviders(
    <WithCodesShown>
      <OptionQuestionEditMode
        question={{ qid: 1, gid: 1, questionThemeName }}
        language="en"
        isFocused={true}
        _children={children}
        handleChildLUpdate={jest.fn()}
        handleChildAdd={jest.fn()}
        handleOnChildDragEnd={jest.fn()}
        handleChildDelete={jest.fn()}
        handleChildCodeUpdate={jest.fn()}
        handleChildAssessmentValueUpdate={jest.fn()}
        {...handlers}
      />
    </WithCodesShown>
  )
  await act(async () => {
    await new Promise((resolve) => setTimeout(resolve, 0))
  })
}

const getRemoveIcons = () =>
  screen
    .getAllByTestId('remove-subquestion-button')
    .map((button) => button.querySelector('.remove-option-button'))

describe('OptionQuestionEditMode on an active survey', () => {
  afterEach(async () => {
    await setSurveyActive(false)
  })

  test('answer option codes of a single choice question are editable', async () => {
    const handleChildCodeUpdate = jest.fn()
    await renderEditMode({
      questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
      children: answers,
      handlers: { handleChildCodeUpdate },
    })
    await setSurveyActive(true)

    const codeInput = screen.getByRole('textbox', {
      name: 'Answer code A2',
    })
    fireEvent.change(codeInput, { target: { value: 'B2' } })

    expect(handleChildCodeUpdate).toHaveBeenCalledWith(
      expect.objectContaining({ newCode: 'B2', childIndex: 1 })
    )
  })

  test('answer options of a single choice question can be added and removed', async () => {
    const handleChildAdd = jest.fn()
    await renderEditMode({
      questionThemeName: getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
      children: answers,
      handlers: { handleChildAdd },
    })
    await setSurveyActive(true)

    const addButton = screen.getByTestId('add-child-button')
    expect(addButton).toBeEnabled()
    fireEvent.click(addButton)
    expect(handleChildAdd).toHaveBeenCalledTimes(1)

    getRemoveIcons().forEach((icon) => expect(icon).not.toHaveClass('d-none'))
  })

  test('subquestion codes of a multiple choice question are read-only', async () => {
    const handleChildAdd = jest.fn()
    await renderEditMode({
      questionThemeName: getQuestionTypeInfo().MULTIPLE_CHOICE.theme,
      children: subquestions,
      handlers: { handleChildAdd },
    })
    await setSurveyActive(true)

    expect(screen.queryByRole('textbox', { name: /code/ })).toBeNull()
    expect(screen.getByText('SQ001')).toHaveClass('question-code-tag')
    expect(screen.getByTestId('add-child-button')).toBeDisabled()
    getRemoveIcons().forEach((icon) => expect(icon).toHaveClass('d-none'))
  })
})
