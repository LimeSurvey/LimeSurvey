import { expect } from '@storybook/test'
import { userEvent } from '@storybook/test'

function getDate(date = new Date()) {
  const day = date.getDate() < 10 ? `0${date.getDate()}` : date.getDate()
  return `${date.getMonth() + 1}/${day}/${date.getFullYear()}`
}

export async function dateTimeTests(step, canvas) {
  const container = canvas.getByTestId('date-time')

  await step('Should select current date', async () => {
    const button = container.querySelector('button')
    await userEvent.click(button)
    await userEvent.keyboard('{Enter}', { delay: 60 })
    const input = container.querySelector('input')
    await expect(input.value).toContain(getDate())
  })
}
