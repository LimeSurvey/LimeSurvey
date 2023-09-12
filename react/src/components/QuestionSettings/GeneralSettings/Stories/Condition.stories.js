import { useState } from 'react'
import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

import { Condition as ConditionAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Basic',
}

export const Condition = () => {
  const [condition, setCondition] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {condition.value}
      </p>
      <ConditionAttribute
        condition={condition}
        update={(changes) => setCondition(changes)}
      />
    </>
  )
}

Condition.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const input = canvas.getByTestId('condition-attribute-input')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we type',
    async () => {
      await userEvent.type(input, 'random condition')
      expect(output.innerHTML).toBe('random condition')
      await userEvent.clear(input)
    }
  )
}
