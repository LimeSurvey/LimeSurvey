import { expect } from '@storybook/test'
import { userEvent } from '@storybook/test'
import { CONSTANTS } from './helpers'

const TEXT = `Lorem Ipsum is simply dummy text of the printing and typesetting industry.
Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
when an unknown printer took a galley of type and scrambled it to make a type specimen book.`

export async function longTextTests(step, canvas) {
  await step(`Should have answer the question`, async () => {
    const answer = canvas.getByTestId('text-question-answer-input')
    await expect(answer).toBeInTheDocument()
    await userEvent.type(answer, TEXT, { delay: CONSTANTS.KEYBOARD_TYPE_DELAY })
    await expect(answer.value).toBe(TEXT)
  })
}
