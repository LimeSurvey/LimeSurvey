import { expect } from '@storybook/test'
import { userEvent } from '@storybook/test'
import { CONSTANTS } from './helpers'

export async function shortTextTests(step, canvas) {
  await step(`Should have answer the question answer`, async () => {
    const answer = canvas.getByTestId('text-question-answer-input')
    await expect(answer).toBeInTheDocument()
    await userEvent.type(answer, 'My name is Tom Riddle.', {
      delay: CONSTANTS.KEYBOARD_TYPE_DELAY,
    })

    await expect(answer.value).toBe('My name is Tom Riddle.')
  })
}
