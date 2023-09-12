import { useState } from 'react'
import { userEvent, within } from '@storybook/testing-library'

import { Suffix as SuffixAttribute } from '../Attributes'
import { expect } from '@storybook/jest'

export default {
  title: 'QuestionAttributes/Layout',
}

export const Suffix = () => {
  const [suffix, setSuffix] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {suffix.value}
      </p>
      <SuffixAttribute
        suffix={suffix}
        update={(changes) => setSuffix(changes)}
      />
    </>
  )
}

Suffix.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const input = canvas.getByTestId('suffix-attribute-input')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we type',
    async () => {
      await userEvent.type(input, 'random suffix')
      expect(output.innerHTML).toBe('random suffix')
      await userEvent.clear(input)
    }
  )
}
