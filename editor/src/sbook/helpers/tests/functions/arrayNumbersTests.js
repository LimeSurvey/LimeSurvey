import { expect } from '@storybook/test'
import { userEvent, waitFor } from '@storybook/test'
import { sleep } from 'helpers/sleep'

const DATA = {
  rows: ['Alex', 'Marina', 'Shi', 'Ahmed'],
  cols: ['Math', ' Science', 'History'],
  answers: [
    ['3', '4', '10'],
    ['1', '5', '4'],
    ['2', '9', '7'],
    ['1', '4', '8'],
  ],
}

export async function arrayNumbersTests(step, canvas) {
  await step('Should able to answer', async () => {
    const rows = canvas.getAllByTestId('drag-and-drop')[1].firstChild.children
    for (let row = 0; row < rows.length; row++) {
      const inputs = rows[row].querySelectorAll('select')
      for (let col = 0; col < inputs.length; col++) {
        const input = inputs[col]
        expect(input).toBeInTheDocument()
        const answer = DATA.answers[row][col]
        userEvent.selectOptions(input, [answer])
        await waitFor(() => {
          expect(input.value).toBe('...')
        })
        await sleep()
      }
    }
    await sleep()
  })
}
