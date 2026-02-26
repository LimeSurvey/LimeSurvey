import { expect } from '@storybook/test'
import { userEvent } from '@storybook/test'
import { CONSTANTS } from './helpers'

export async function askQuestion(question, questionTitle) {
  await userEvent.keyboard(question, {
    delay: CONSTANTS.KEYBOARD_TYPE_DELAY,
  })
  await expect(questionTitle.innerText.trim()).toBe(question)
}
