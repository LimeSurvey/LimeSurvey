import { useState } from 'react'
import { expect } from '@storybook/jest'
import { userEvent, within } from '@storybook/testing-library'

import { Prefix as PrefixAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Layout',
}

export const Prefix = () => {
  const [prefix, setPrefix] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {prefix.value}
      </p>
      <PrefixAttribute
        prefix={prefix}
        update={(changes) => setPrefix(changes)}
      />
    </>
  )
}

Prefix.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const input = canvas.getByTestId('prefix-attribute-input')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we type',
    async () => {
      await userEvent.type(input, 'random prefix')
      expect(output.innerHTML).toBe('random prefix')
      await userEvent.clear(input)
    }
  )
}
