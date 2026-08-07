import { expect } from '@storybook/test'
import { getNoAnswerLabel } from 'helpers'

export async function yesNoTests(step, canvas) {
  const container = canvas.getByTestId('yes-no-question')
  const buttons = canvas.getAllByTestId(`yes-no-question-answer`)

  await step('Should render yes no question correctly', async () => {
    await expect(container).toBeInTheDocument()
  })

  await step('Should find 3 buttons', async () => {
    await expect(buttons.length).toBe(3)
  })

  await step(
    'Expect buttons to have the correct texts and values',
    async () => {
      await expect(buttons[0]).toHaveTextContent('Yes')
      await expect(buttons[0]).toHaveValue('yes')

      await expect(buttons[1]).toHaveTextContent('No')
      await expect(buttons[1]).toHaveValue('no')

      await expect(buttons[2]).toHaveTextContent(getNoAnswerLabel(true))
      await expect(buttons[2]).toHaveValue('')
    }
  )
}
