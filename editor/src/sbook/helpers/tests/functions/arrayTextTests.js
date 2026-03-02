import { expect } from '@storybook/test'
import { userEvent, waitFor } from '@storybook/test'
import { sleep } from 'helpers/sleep'

function random() {
  return Math.floor(Math.random() * 20).toString()
}

const DATA = {
  rows: ['Alex', 'Marina', 'Shi', 'Ahmed'],
  cols: ['Math', ' Science', 'History'],
  answers: [
    [random(), random(), random()],
    [random(), random(), random()],
    [random(), random(), random()],
    [random(), random(), random()],
  ],
}

export async function arrayTextTests(step, canvas) {
  await step('Should able to answer', async () => {
    const rows = canvas.getAllByTestId('drag-and-drop')[1].firstChild.children
    await sleep()
    if (rows && rows.length) {
      for (let row = 0; row < rows.length; row++) {
        const inputs = rows[row].querySelectorAll('input')
        for (let col = 0; col < inputs.length; col++) {
          const input = inputs[col]
          expect(input).toBeInTheDocument()
          const answer = DATA.answers[row][col]
          await userEvent.type(input, answer, {
            delay: 60,
          })
          await waitFor(() => {
            expect(input.value).toBe(answer)
          })
          await sleep()
        }
      }
    }
    await sleep()
  })
}
