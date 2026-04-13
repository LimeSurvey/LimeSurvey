import { arrayDualQuestion } from '../arrayDualQuestion'

const ROWS = ['Cao Cao', 'Lu Bu', 'Shi', 'Hannibal', 'Napoleon']
const COLS_1 = ['Africa', 'Asia', 'Europe']
const COLS_2 = ['Tunisia', 'China']

// async function radio(container, row, col) {
//   const rowDiv = container.firstChild.children[row]
//   const inputs = rowDiv.querySelectorAll('input')
//   await expect(inputs[col].checked).toBe(false)
//   await userEvent.click(inputs[col])
//   await expect(inputs[col].checked).toBe(true)
//   await sleep()
// }

export async function arrayDualScaleTests(step, canvas) {
  await arrayDualQuestion(step, canvas, {
    rows: ROWS,
    cols1: COLS_1,
    cols2: COLS_2,
  })

  canvas.getAllByTestId('drag-and-drop')[1]

  // await step('Should able to choose Asia for Cao Cao', async () => {
  //   await radio(table1, 0, 1)
  // })

  // await step('Should able to choose Asia for Lu Bu', async () => {
  //   await radio(table1, 1, 1)
  // })

  // await step('Should able to choose Asia for Shi', async () => {
  //   await radio(table1, 2, 1)
  // })

  // await step('Should able to choose Africa for Hannibal', async () => {
  //   await radio(table1, 3, 0)
  // })

  // await step('Should able to choose Europe for Napoleon', async () => {
  //   await radio(table1, 4, 2)
  // })

  // const table2 = canvas.getAllByTestId('drag-and-drop')[3]

  // await step('Should able to choose China for Cao Cao', async () => {
  //   await radio(table2, 0, 1)
  // })

  // await step('Should able to choose China for Lu Bu', async () => {
  //   await radio(table2, 1, 1)
  // })

  // await step('Should able to choose China for Shi', async () => {
  //   await radio(table2, 2, 1)
  // })

  // await step('Should delete the last row', async () => {
  //   const draggableContainer = canvas.getAllByTestId('drag-and-drop')
  //   const rowElements = draggableContainer[1].firstChild.children
  //   const deleteButton = rowElements[rowElements.length - 1].querySelector(
  //     '.remove-item-button'
  //   )
  //   await userEvent.click(deleteButton)
  //   await expect(draggableContainer[1].firstChild.children).toHaveLength(
  //     ROWS.length - 1
  //   )
  // })

  // await step('Should able to choose Tunisia for Hannibal', async () => {
  //   await radio(table2, 3, 0)
  // })
}
