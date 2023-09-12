import { useState } from 'react'
import { expect } from '@storybook/jest'
import { userEvent, within } from '@storybook/testing-library'

import { NumbersOnly as NumbersOnlyAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Basic',
}

export const NumbersOnly = () => {
  const [numbersOnly, setNumbersOnly] = useState({ value: false })

  return (
    <>
      <p className="d-none" data-testid="output">
        {numbersOnly.value === true && 'true'}
        {numbersOnly.value === false && 'false'}
      </p>
      <NumbersOnlyAttribute
        numbers_only={numbersOnly}
        update={(value) => setNumbersOnly(value)}
      />
    </>
  )
}

NumbersOnly.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const onBtn = canvas.getByTestId(
    'numbers-only-attribute-question-settings-on-toggler'
  )
  const offBtn = canvas.getByTestId(
    'numbers-only-attribute-question-settings-off-toggler'
  )
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include an value property when we click on the on button with value "true"',
    async () => {
      await userEvent.click(onBtn)
      expect(output.innerHTML).toBe('true')
    }
  )

  await step(
    'Expect the callback output to include an value property when we click on the off button with value "false"',
    async () => {
      await userEvent.click(offBtn)
      expect(output.innerHTML).toBe('false')
    }
  )
}
