import { expect } from '@storybook/test'
import { userEvent } from '@storybook/test'
import { sleep } from 'helpers/sleep'
import { CONSTANTS } from './helpers'

const OPTIONS = {
  useSolution: 1,
}

export async function clearText(text, options = OPTIONS) {
  await expect(text).toBeInTheDocument()
  if (options.useSolution === 1) {
    await userEvent.tripleClick(text)
    await sleep()
    await userEvent.keyboard('{Delete}')
    await sleep()
    await expect(text.innerText.trim()).toBe('')
    await sleep()
  } else if (options.useSolution === 2) {
    const lgth = text.textContent.length
    await userEvent.tab()
    for (let index = 0; index < lgth; index++) {
      await userEvent.keyboard('{Delete}', {
        delay: CONSTANTS.KEYBOARD_TYPE_DELAY,
      })
    }
    await expect(text.innerText.trim()).toBe('')
    await sleep()
  }
}
