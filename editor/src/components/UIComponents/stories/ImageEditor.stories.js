import { useState } from 'react'
import { ImageEditor } from '../ImageEditor/ImageEditor'

export default {
  title: 'UIComponents/ImageEditor',
  component: ImageEditor,
}

const FILE = {
  origin: 'image3.jpg',
  zoom: 1,
  rotate: 0,
  radius: 0,
}

export function Basic() {
  const [selectedFile, setSelectedFile] = useState(FILE)

  return (
    <ImageEditor
      showModal={true}
      onChange={(file) => {
        setSelectedFile({ ...file })
      }}
      file={selectedFile}
    />
  )
}

// Basic.play = async ({ canvasElement, step }) => {
//   const canvas = within(canvasElement)
//   const button = canvas.getByRole('button')
//   await step('Should render a button', async () => {
//     await expect(button).toBeInTheDocument()
//   })

//   await step('Should open a dialog', async () => {
//     await userEvent.click(button)
//     await sleep()
//     const dialog = screen.getByRole('dialog')
//     await expect(dialog).toBeInTheDocument()
//   })

//   await step(
//     'Should find 3 input ranges with correct label and value',
//     async () => {
//       const inputChangeContainers = screen.getAllByTestId('input-range')
//       await expect(inputChangeContainers.length).toBe(3)
//       const file = FILE
//       // const { origin, ...file } = FILE
//       let count = 0
//       for (const key in file) {
//         const input = inputChangeContainers[count].querySelector('input')
//         const label = inputChangeContainers[count].querySelector('label')
//         await expect(label).toBeInTheDocument()
//         await expect(label.innerHTML.toLowerCase()).toBe(key)
//         await expect(input.value).toBe(`${file[key]}`)
//         count++
//       }
//     }
//   )

//   await step('Should handle zoom change', async () => {
//     const inputChangeContainers = screen.getAllByTestId('input-range')
//     const input = inputChangeContainers[0].querySelector('input')
//     await userEvent.type(input, '20')
//     await expect(input.value).toBe('4')
//     await sleep()
//     input.value = ''
//     await userEvent.type(input, '2')
//     await expect(input.value).toBe('2')
//     await sleep()
//   })

//   await step('Should handle rotate change', async () => {
//     const inputChangeContainers = screen.getAllByTestId('input-range')
//     const input = inputChangeContainers[1].querySelector('input')
//     await userEvent.type(input, '1000')
//     await expect(input.value).toBe('360')
//     await sleep()
//     input.value = ''
//     await userEvent.type(input, '90')
//     await expect(input.value).toBe('90')
//     await sleep()
//   })

//   await step('Should handle radius change', async () => {
//     const inputChangeContainers = screen.getAllByTestId('input-range')
//     const input = inputChangeContainers[2].querySelector('input')
//     await userEvent.type(input, '1000')
//     await expect(input.value).toBe('100')
//     await sleep()
//     input.value = ''
//     await userEvent.type(input, '50')
//     await expect(input.value).toBe('50')
//     await sleep()
//   })

//   await step('Should handle save changes', async () => {
//     const saveButton = screen.getByText('Save Changes')
//     await expect(saveButton).toBeInTheDocument()
//     await userEvent.click(saveButton)
//   })
// }
