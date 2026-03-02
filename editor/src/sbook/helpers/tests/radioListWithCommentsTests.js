import { expect } from '@storybook/test'
import { userEvent } from '@storybook/test'

export async function radioListWithComments(step, canvas) {
  await step('Should write a comment', async () => {
    const comment = 'This the comment for the question.'
    const input = canvas.getByTestId('child-ui-component')
    await expect(input).toBeInTheDocument()
    await userEvent.type(input, comment)
    // await expect(input.value).toBe(comment)
  })
}
