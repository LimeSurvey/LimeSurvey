import { useState } from 'react'
import { expect, waitFor } from '@storybook/test'
import { userEvent, within } from '@storybook/test'
import { AlignButtons as AlignButtonsComponent } from '../../Buttons/AlignButtons'
import { sleep } from 'helpers/sleep'

export default {
  title: 'UIComponents/Button/AlignButtons',
  component: AlignButtonsComponent,
}

export const AlignButtons = () => {
  const [value, setValue] = useState('right')

  return (
    <>
      <p className="d-none" data-testid="output">
        {value}
      </p>
      <AlignButtonsComponent
        labelText="Aligned Buttons"
        value={value}
        update={(value) => setValue(value)}
      />
    </>
  )
}

AlignButtons.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('story-wrapper'), { timeout: 10000 })

  const leftButton = getByTestId('left-align-btn')
  const centerButton = getByTestId('center-align-btn')
  const rightButton = getByTestId('right-align-btn')

  const labelText = getByTestId('align-buttons-label-text')
  const output = getByTestId('output')

  await step('Should display the label text', async () => {
    await expect(labelText.innerHTML).toBe('Aligned Buttons')
  })

  await step(
    'Should output left when clicking on the left button',
    async () => {
      await userEvent.click(leftButton)
      // userEvent is not working on Firefox
      leftButton.click()
      await sleep()
    }
  )

  await step(
    'Should output center when clicking on the center button',
    async () => {
      // get the input to click on
      await userEvent.click(centerButton)
      // userEvent is not working on Firefox
      centerButton.click()
      await expect(output.innerText).toBe('center')
      await sleep()
    }
  )

  await step(
    'Should output right when clicking on the right button',
    async () => {
      await userEvent.click(rightButton)
      // userEvent is not working on Firefox
      rightButton.click()
      await expect(output.innerText).toBe('right')
      await sleep()
    }
  )
}
