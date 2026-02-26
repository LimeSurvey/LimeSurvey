import { expect, waitFor, userEvent, within } from '@storybook/test'

import { sleep } from 'helpers/sleep'
import { DateTimePickerComponent } from '../DateTimePicker/DateTimePicker'

export default {
  title: 'UIComponents/DateTimePicker',
  component: DateTimePickerComponent,
}

export const DateTimePicker = (args) => {
  return <DateTimePickerComponent {...args} />
}

DateTimePicker.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await waitFor(() => canvas.getByTestId('story-wrapper'), { timeout: 10000 })

  await step('Should render without a label', async () => {
    const container = canvas.getByTestId('data-time-picker')
    await expect(container).toBeInTheDocument()

    const picker = container.querySelector('input')
    await sleep()
    await userEvent.click(picker)
  })

  await step('Should reopen the picker', async () => {
    await sleep()
    const container = canvas.getByTestId('data-time-picker')
    const button = container.querySelector('button')
    await userEvent.click(button)
    await expect(container).toBeInTheDocument()
  })
}
