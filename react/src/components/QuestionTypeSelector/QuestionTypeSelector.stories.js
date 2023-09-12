import { expect } from '@storybook/jest'
import { userEvent } from '@storybook/testing-library'
import { within } from '@testing-library/react'

import {
  QuestionTypeInfo,
  QuestionTypeTitle,
} from 'components/Survey/QuestionTypes'

import { QuestionTypeSelector } from './QuestionTypeSelector'

let selectedType

export default {
  title: 'General/QuestionTypeSelector',
  component: QuestionTypeSelector,
}

export const Basic = () => (
  <QuestionTypeSelector
    callBack={(type) => {
      selectedType = type
    }}
  />
)

Basic.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const questionTypeSearchInput = canvas.getByTestId('question-type-search')

  await step('Should use the labels exactly one time', () => {
    for (const key of Object.keys(QuestionTypeInfo)) {
      const questionTypeInputs = canvas.getAllByTestId(
        `question-type-${QuestionTypeTitle[QuestionTypeInfo[key]]}`,
        { exact: false }
      )

      expect(questionTypeInputs.length).toBe(1)
    }
  })

  await step('Should be mapping the labels to the correct values', () => {
    for (const key of Object.keys(QuestionTypeInfo)) {
      const questionTypeInput = canvas.getByTestId(
        `question-type-${QuestionTypeTitle[QuestionTypeInfo[key]]}`,
        { exact: false }
      )

      userEvent.click(questionTypeInput)
      expect(selectedType).toBe(QuestionTypeInfo[key])
    }
  })

  await step('should be able to search for the text questions', () => {
    userEvent.type(questionTypeSearchInput, 'text')
    const questionTypeInputs = canvas.getAllByTestId(
      `question-type-selector-label`,
      { exact: false }
    )

    expect(questionTypeInputs.length).toBe(3)

    questionTypeInputs.forEach((questionInput) => {
      expect(questionInput.innerHTML.toLocaleLowerCase().includes('text')).toBe(
        true
      )
    })
  })

  await step('should be able to search with capital letters', () => {
    userEvent.clear(questionTypeSearchInput)
    userEvent.type(questionTypeSearchInput, 'TEXT')
    const questionTypeInputs = canvas.getAllByTestId(
      `question-type-selector-label`,
      { exact: false }
    )

    expect(questionTypeInputs.length).toBe(3)

    questionTypeInputs.forEach((questionInput) => {
      expect(questionInput.innerHTML.toLocaleLowerCase().includes('text')).toBe(
        true
      )
    })
  })

  await step(
    'Should focus the labels when using the up/down arrow keys',
    () => {
      const questionTypeInputs = canvas.getAllByTestId(
        `question-type-selector-label`,
        { exact: false }
      )

      questionTypeInputs.forEach((input) => {
        expect(input.classList.contains('focus-element')).toBe(false)
      })

      userEvent.keyboard('[ArrowDown]')
      expect(questionTypeInputs[0].classList.contains('focus-element')).toBe(
        true
      )

      userEvent.keyboard('[ArrowDown]')
      expect(questionTypeInputs[1].classList.contains('focus-element')).toBe(
        true
      )
      expect(questionTypeInputs[0].classList.contains('focus-element')).toBe(
        false
      )
    }
  )

  await step(
    'Should be able to select question types using the Enter key',
    () => {
      userEvent.keyboard('[Enter]')
      expect(selectedType).toBe('T')
      userEvent.clear(questionTypeSearchInput)
    }
  )
}
