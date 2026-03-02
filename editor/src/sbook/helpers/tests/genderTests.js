import { expect } from '@storybook/test'
import { sleep } from 'helpers/sleep'

export async function genderTests(step, canvas) {
  const container = canvas.getByTestId('gender-question')
  await step('Should render GenderQuestion correctly', async () => {
    await expect(container).toBeInTheDocument()
    await sleep()
  })
}
