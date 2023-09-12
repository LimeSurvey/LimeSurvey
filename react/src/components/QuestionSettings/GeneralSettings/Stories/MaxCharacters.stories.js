import { useState } from 'react'
import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

import { MaximumCharacters as MaximumCharactersAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Basic',
}

export const MaximumCharacters = () => {
  const [maxCharacters, setMaxCharacters] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {maxCharacters.value}
      </p>
      <MaximumCharactersAttribute
        maximum_chars={maxCharacters}
        update={(changes) => {
          setMaxCharacters(changes)
        }}
      />
    </>
  )
}

MaximumCharacters.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const input = canvas.getByTestId('maximum-characters-attribute-input')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we type',
    async () => {
      await userEvent.type(input, '123')
      expect(output.innerHTML).toBe('123')
      await userEvent.clear(input)
    }
  )
}
