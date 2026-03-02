import { userEvent, waitFor, within } from '@storybook/test'
import { InputRange } from '../InputRange/InputRange'
import { expect } from '@storybook/test'
import { sleep } from 'helpers/sleep'
import { Direction } from 'react-range'

export default {
  title: 'UIComponents/InputRange',
  component: InputRange,
}

export function Basic() {
  return <InputRange direction={Direction.Down} />
}

Basic.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await waitFor(() => canvas.getAllByTestId('story-wrapper'))
  const container = canvas.getByTestId('input-range')
  const input = container.querySelector('input')

  await step(
    'Should render Input Range without a label correctly',
    async () => {
      await expect(input).toBeInTheDocument()
      await expect(container).toBeInTheDocument()
      await expect(container.querySelector('label')).toBeNull()
    }
  )

  await step('Should render "10.0"', async () => {
    await userEvent.type(input, '10', { delay: 60 })
    await expect(canvas.getByTestId('input-range-value').innerText).toBe('10.0')
    await sleep()
  })

  await step('Should clear Input Range', async () => {
    await userEvent.clear(input)
    await expect(canvas.getByTestId('input-range-value').innerText).toBe('0.0')
    await sleep()
  })
}

export function Horizontal() {
  return <InputRange labelText={'Input Range'} direction={Direction.Right} />
}

Horizontal.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await waitFor(() => canvas.getAllByTestId('story-wrapper'))

  const container = canvas.getByTestId('input-range')
  const input = container.querySelector('input')
  const slider = canvas.getByRole('slider')

  await step('Should render Input Range with a label correctly', () => {
    expect(container).toBeInTheDocument()
    expect(container.querySelector('label')).toBeDefined()
    expect(container.querySelector('label').innerHTML).toBe('Input Range')
    expect(input).toBeInTheDocument()
    expect(slider).toBeInTheDocument()
  })
}
