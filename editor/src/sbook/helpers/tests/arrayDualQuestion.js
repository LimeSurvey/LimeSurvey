// async function insert(canvas, el, lgth) {
//   const element =
//     el === 'rows'
//       ? canvas.getByText('Add subquestion')
//       : el === 'cols1'
//         ? canvas.getAllByText('Add answer option')[0]
//         : el === 'cols2'
//           ? canvas.getAllByText('Add answer option')[1]
//           : null

//   if (!element) return

//   const len = el === 'cols2' ? lgth - 1 : lgth
//   for (let index = 0; index < len; index++) {
//     await userEvent.click(element)
//     await sleep()
//   }
//   await sleep()
// }

// async function insertTitle(array, titles, el) {
//   for (let index = 0; index < array.length; index++) {
//     const editable = array[index].querySelector('.content-editable')
//     await expect(editable).toBeInTheDocument()

//     if (editable.innerHTML.length) {
//       await clearText(editable)
//     }

//     const title =
//       titles && titles.length
//         ? titles[index]
//         : el === 'row'
//           ? `Row ${index + 1}`
//           : el === 'col'
//             ? `Col ${index + 1}`
//             : ''

//     await userEvent.type(editable, title, {
//       delay: 60,
//     })
//     await expect(editable.textContent).toBe(title)
//     await sleep()
//   }
//   await sleep()
// }

export async function arrayDualQuestion(_, canvas) {
  canvas.getAllByTestId('drag-and-drop')

  // await step('Should find 4 draggable containers', async () => {
  //   await expect(draggableContainer.length).toBe(4)
  // })

  // const rows = options.rows.length
  // const cols1 = options.cols1.length
  // const cols2 = options.cols2.length

  // if (options.startWith === 'rows') {
  //   await step(`Should add ${rows} rows`, async () => {
  //     await insert(canvas, 'rows', rows)
  //   })
  //   await step(`Should add ${cols1} columns`, async () => {
  //     await insert(canvas, 'cols1', cols1)
  //   })
  //   await step(`Should add ${cols2} columns`, async () => {
  //     await insert(canvas, 'cols2', cols2)
  //   })
  // } else {
  //   await step(`Should add ${cols1} columns`, async () => {
  //     await insert(canvas, 'cols1', cols1)
  //   })
  //   await step(`Should add ${cols2} columns`, async () => {
  //     await insert(canvas, 'cols2', cols2)
  //   })

  //   await step(`Should add ${rows} rows`, async () => {
  //     await insert(canvas, 'rows', rows)
  //   })
  // }

  // await step('Should able to write the table row titles', async () => {
  //   const rowElements = draggableContainer[1].firstChild.children
  //   await insertTitle(rowElements, options.rows, 'row')
  // })

  // await step('Should able to write the table col titles', async () => {
  //   const colElements = draggableContainer[0].firstChild.children
  //   await insertTitle(colElements, options.cols1, 'col')
  // })

  // await step('Should able to write the table col titles', async () => {
  //   const colElements = draggableContainer[2].firstChild.children
  //   await insertTitle(colElements, options.cols2, 'col')
  // })
}
