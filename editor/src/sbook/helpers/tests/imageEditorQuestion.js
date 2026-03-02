import { expect } from '@storybook/test'
import { userEvent } from '@storybook/test'
import { sleep } from 'helpers/sleep'

const OPTIONS = {
  file: {
    origin: 'image2.jpg',
    name: 'imag2',
    zoom: 1,
    rotate: 0,
    radius: 0,
  },
}

export async function imageEditorQuestion(step, canvas, options = OPTIONS) {
  const container = canvas.getByTestId('file-upload')

  await step('Should open a dialog', async () => {
    const images = container.querySelectorAll('.image-handle-btn-wrapper')
    const deleteButton = images[0].querySelectorAll('button')[0]
    await expect(deleteButton).toBeInTheDocument()
    await userEvent.click(deleteButton)
    await sleep()
    const dialog = screen.getByRole('dialog')
    await expect(dialog).toBeInTheDocument()
  })

  await step(
    'Should find 3 input ranges with correct label and value',
    async () => {
      const inputChangeContainers = screen.getAllByTestId('input-range')
      await expect(inputChangeContainers.length).toBe(3)
      // const { origin, name, ...file } = options.file
      const file = options.file
      let count = 0
      for (const key in file) {
        const input = inputChangeContainers[count].querySelector('input')
        const label = inputChangeContainers[count].querySelector('label')
        await expect(label).toBeInTheDocument()
        await expect(label.innerHTML.toLowerCase()).toBe(key)
        await expect(input.value).toBe(`${file[key]}`)
        count++
      }
    }
  )

  await step('Should handle zoom change', async () => {
    const inputChangeContainers = screen.getAllByTestId('input-range')
    const input = inputChangeContainers[0].querySelector('input')
    await userEvent.type(input, '20')
    await expect(input.value).toBe('4')
    await sleep()
    input.value = ''
    await userEvent.type(input, '1')
    await expect(input.value).toBe('1')
    await sleep()
  })

  await step('Should handle rotate change', async () => {
    const inputChangeContainers = screen.getAllByTestId('input-range')
    const input = inputChangeContainers[1].querySelector('input')
    await userEvent.type(input, '1000')
    await expect(input.value).toBe('360')
    await sleep()
    input.value = ''

    for (let index = 360; index >= 180; index -= 20) {
      await userEvent.type(input, `${index}`)
      await expect(input.value).toBe(`${index}`)
      await sleep(180)
      if (index === 180) break
      input.value = ''
    }
    await sleep()
  })

  await step('Should handle radius change', async () => {
    const inputChangeContainers = screen.getAllByTestId('input-range')
    const input = inputChangeContainers[2].querySelector('input')
    await userEvent.type(input, '1000')
    await expect(input.value).toBe('100')
    await sleep()
    input.value = ''
    await userEvent.type(input, '5')
    await expect(input.value).toBe('5')
    await sleep()
  })

  /*
  await step('Should handle drag of the picture', async () => {
    const dialog = screen.getByRole('dialog')
    const picture = dialog.querySelector('canvas')
    expect(picture).toBeInTheDocument()

    fireEvent.mouseDown(picture, {
      clientY: 0,
      clientX: 120,
    })
    await waitFor(() => {})
    await sleep(2000)

    fireEvent.mouseDown(picture, {
      clientY: 64,
      clientX: -120,
    })
    await waitFor(() => {})

    await sleep(2000)
  })
  */

  await step('Should handle save changes', async () => {
    const saveButton = screen.getByText('Save changes')
    await expect(saveButton).toBeInTheDocument()
    await userEvent.click(saveButton)
    await sleep()
  })
}
