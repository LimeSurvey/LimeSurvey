import { useState } from 'react'
import { expect } from '@storybook/jest'
import { userEvent, within } from '@storybook/testing-library'

import { HideTip as HideTipAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Layout',
}

export const HideTip = () => {
  const [hideTip, setHideTip] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {hideTip.value === true && 'true'}
        {hideTip.value === false && 'false'}
      </p>
      <HideTipAttribute
        hide_tip={hideTip}
        update={(changes) => setHideTip(changes)}
      />
    </>
  )
}

HideTip.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const onBtn = canvas.getByTestId('hide-tip-question-settings-on-toggler')
  const offBtn = canvas.getByTestId('hide-tip-question-settings-off-toggler')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we click on the on button with value "true"',
    async () => {
      await userEvent.click(onBtn)
      expect(output.innerHTML).toBe('true')
    }
  )

  await step(
    'Expect the callback output to include a value property when we click on the on button with value "false"',
    async () => {
      await userEvent.click(offBtn)
      expect(output.innerHTML).toBe('false')
    }
  )
}
