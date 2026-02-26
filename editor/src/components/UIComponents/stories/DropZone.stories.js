import { expect, waitFor } from '@storybook/test'
import { DropZone as DropZoneComponent } from '../DropZone/DropZone'
import { userEvent, within } from '@storybook/test'
import { sleep } from 'helpers/sleep'
import { getNoAnswerLabel } from 'helpers'

export default {
  title: 'UIComponents/DropZone',
  component: DropZoneComponent,
}

export const DropZone = () => {
  return (
    <DropZoneComponent
      labelText="Drop zone label"
      image={getNoAnswerLabel(true)}
      onReaderResult={() => {}}
    />
  )
}

DropZone.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('dropzone'))

  const container = getByTestId('dropzone')
  const input = container.querySelector('input')

  await step('Should render DropZone with a label', async () => {
    await expect(container).toBeInTheDocument()
    await expect(input).toBeInTheDocument()
    const label = container.querySelector('label')
    await expect(label.innerHTML).toBe('Drop zone label')
    await sleep()
  })

  await step('Should handle upload', async () => {
    const file = new File(['image'], 'image.png', { type: 'image/png' })
    await userEvent.upload(input, file)
    await expect(input.files[0]).toStrictEqual(file)
    await expect(input.files.item(0)).toStrictEqual(file)
    await expect(input.files).toHaveLength(1)
    await sleep()
  })
}
