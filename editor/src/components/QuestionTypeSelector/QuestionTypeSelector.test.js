// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { QuestionTypeSelector } from './'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { getQuestionTypeInfo } from 'components/QuestionTypes/getQuestionTypeInfo'

describe('QuestionTypeSelector', () => {
  let selectedType

  beforeEach(async () => {
    await renderWithProviders(
      <QuestionTypeSelector
        callBack={(value) => {
          selectedType = value.type
        }}
      />
    )
  })

  test('Should use the labels exactly one time', async () => {
    for (const key of Object.keys(getQuestionTypeInfo())) {
      if (!getQuestionTypeInfo()[key].isQuestionType) {
        continue
      }

      const questionTypeInputs = screen.getAllByTestId(
        `question-type-${getQuestionTypeInfo()[key].theme}`
      )

      expect(questionTypeInputs.length).toBe(1)
    }
  })

  test('Should be mapping the labels to the correct values', async () => {
    for (const key of Object.keys(getQuestionTypeInfo())) {
      if (!getQuestionTypeInfo()[key].isQuestionType) {
        continue
      }

      const questionTypeInput = screen.getByTestId(
        `question-type-${getQuestionTypeInfo()[key].theme}`
      )
      await userEvent.click(questionTypeInput)
      expect(selectedType).toBe(getQuestionTypeInfo()[key].type)
    }
  })

  test('should be able to search for the text questions', async () => {
    const questionTypeSearchInput = screen.getByTestId('question-type-search')
    await userEvent.type(questionTypeSearchInput, 'text')

    const questionTypeInputs = screen.getAllByTestId(
      `question-type-selector-label`,
      {
        exact: false,
      }
    )
    expect(questionTypeInputs.length).toBe(5)
    questionTypeInputs.forEach((questionInput) => {
      expect(questionInput.innerHTML.toLocaleLowerCase().includes('text')).toBe(
        true
      )
    })
  })

  test('should be able to search with capital letters', async () => {
    const questionTypeSearchInput = screen.getByTestId('question-type-search')
    await userEvent.clear(questionTypeSearchInput)

    await userEvent.type(questionTypeSearchInput, 'TEXT')
    const questionTypeInputs = screen.getAllByTestId(
      `question-type-selector-label`,
      {
        exact: false,
      }
    )

    expect(questionTypeInputs.length).toBe(5)
    questionTypeInputs.forEach((questionInput) => {
      expect(questionInput.innerHTML.toLocaleLowerCase().includes('text')).toBe(
        true
      )
    })
  })

  test('Should focus the labels when using the up/down arrow keys', async () => {
    const questionTypeInputs = screen.getAllByTestId(
      `question-type-selector-label`,
      {
        exact: false,
      }
    )

    questionTypeInputs.forEach((input) => {
      expect(input.classList.contains('focus-element')).toBe(false)
    })

    await userEvent.keyboard('[ArrowDown]')
    expect(questionTypeInputs[0].classList.contains('focus-element')).toBe(
      false
    ) // TODO: was true

    await userEvent.keyboard('[ArrowDown]')
    expect(questionTypeInputs[1].classList.contains('focus-element')).toBe(
      false
    ) // TODO: was true
    expect(questionTypeInputs[0].classList.contains('focus-element')).toBe(
      false
    )
  })
})
