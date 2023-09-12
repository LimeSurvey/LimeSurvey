import { useState } from 'react'

import { RegExp as RegExpAttribute } from '../Attributes'
import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

export default {
  title: 'QuestionAttributes/Basic',
}

export const RegExp = () => {
  const [regExp, setRegExp] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {regExp.value}
      </p>
      <RegExpAttribute
        reg_exp={regExp}
        update={(changes) => {
          setRegExp(changes)
        }}
      />
    </>
  )
}

RegExp.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const input = canvas.getByTestId('reg-exp-attribute-input')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we type',
    async () => {
      await userEvent.type(input, 'random regexp')
      expect(output.innerHTML).toBe('random regexp')
      await userEvent.clear(input)
    }
  )
}
