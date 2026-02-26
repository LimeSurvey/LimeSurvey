import { expect } from '@storybook/test'
import { sleep } from 'helpers/sleep'

async function select(list, radioIndex) {
  await expect(list[radioIndex - 1].click).toBeDefined()
  list[radioIndex - 1].click()

  for (let index = 0; index < list.length; index++) {
    if (index === radioIndex - 1) {
      await expect(list[index].checked).toBe(true)
    } else {
      await expect(list[index].checked).toBe(false)
    }
  }
  await sleep()
}

export async function fivePointChoice(step, canvas) {
  const radios = canvas.getAllByTestId('five-point-choice-question-answer')

  await step('Should find 6 radios buttons', async () => {
    await expect(radios.length).toBe(6)
  })

  await step('Should select the fourth one', async () => {
    await select(radios, 4)
  })

  await step('Should select the first one', async () => {
    await select(radios, 1)
  })

  await step('Should select the last one', async () => {
    await select(radios, 6)
  })
}
