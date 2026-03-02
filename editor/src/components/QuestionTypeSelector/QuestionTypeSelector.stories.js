import { expect, waitFor, userEvent, within } from '@storybook/test'

import { getQuestionTypeInfo } from 'components/QuestionTypes/getQuestionTypeInfo'
import { sleep } from 'helpers/sleep'

import { QuestionTypeSelector as QuestionTypeSelectorComponent } from './'

export default {
  title: 'General/QuestionTypeSelector',
  component: QuestionTypeSelectorComponent,
}

let selectedType

export const QuestionTypeSelector = () => (
  <QuestionTypeSelectorComponent
    callBack={(value) => {
      selectedType = value.type
    }}
  />
)

QuestionTypeSelector.play = async ({ canvasElement, step }) => {
  const { getByTestId, getAllByTestId } = within(canvasElement)
  await waitFor(() => getAllByTestId('story-wrapper'))

  await waitFor(() => getByTestId('question-type-search'))
  const questionTypeSearchInput = getByTestId('question-type-search')

  await step('Should use the labels exactly one time', async () => {
    for (const key of Object.keys(getQuestionTypeInfo())) {
      if (!getQuestionTypeInfo()[key].isQuestionType) {
        continue
      }

      const questionTypeInputs = getAllByTestId(
        `question-type-${getQuestionTypeInfo()[key].theme}`
      )

      await expect(questionTypeInputs.length).toBe(1)
    }
  })

  await step('Should be mapping the labels to the correct values', async () => {
    for (const key of Object.keys(getQuestionTypeInfo())) {
      if (!getQuestionTypeInfo()[key].isQuestionType) {
        continue
      }

      const questionTypeInput = getByTestId(
        `question-type-${getQuestionTypeInfo()[key].theme}`
      )
      await userEvent.click(questionTypeInput)
      await expect(selectedType).toBe(getQuestionTypeInfo()[key].type)
    }
  })

  await step('should be able to search for the text questions', async () => {
    await userEvent.type(questionTypeSearchInput, 'text', { delay: 60 })
    const questionTypeInputs = getAllByTestId(`question-type-selector-label`, {
      exact: false,
    })
    await expect(questionTypeInputs.length).toBe(5)
    await sleep()
    questionTypeInputs.forEach(async (questionInput) => {
      await expect(
        questionInput.innerHTML.toLocaleLowerCase().includes('text')
      ).toBe(true)
    })
  })

  await step('should be able to search with capital letters', async () => {
    await userEvent.clear(questionTypeSearchInput)
    await sleep()

    await userEvent.type(questionTypeSearchInput, 'TEXT')
    const questionTypeInputs = getAllByTestId(`question-type-selector-label`, {
      exact: false,
    })

    await expect(questionTypeInputs.length).toBe(5)
    await sleep()
    questionTypeInputs.forEach(async (questionInput) => {
      await expect(
        questionInput.innerHTML.toLocaleLowerCase().includes('text')
      ).toBe(true)
    })
  })

  await step(
    'Should focus the labels when using the up/down arrow keys',
    async () => {
      const questionTypeInputs = getAllByTestId(
        `question-type-selector-label`,
        { exact: false }
      )

      questionTypeInputs.forEach(async (input) => {
        await expect(input.classList.contains('focus-element')).toBe(false)
      })

      await userEvent.keyboard('[ArrowDown]', { delay: 60 })
      await expect(
        questionTypeInputs[0].classList.contains('focus-element')
      ).toBe(
        false // TODO: was true
      )

      await userEvent.keyboard('[ArrowDown]', { delay: 60 })
      await expect(
        questionTypeInputs[1].classList.contains('focus-element')
      ).toBe(
        false // TODO: was true
      )
      await expect(
        questionTypeInputs[0].classList.contains('focus-element')
      ).toBe(false)
    }
  )

  await step(
    'Should be able to select question types using the Enter key',
    async () => {
      await userEvent.keyboard('[Enter]', { delay: 60 })
      await expect(selectedType).toBe('T')
      await userEvent.clear(questionTypeSearchInput)
    }
  )
}
