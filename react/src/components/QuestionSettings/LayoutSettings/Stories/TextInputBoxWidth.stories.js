import { useState } from 'react'
import { expect } from '@storybook/jest'
import { userEvent, within } from '@storybook/testing-library'

import { TextInputBoxWidth as TextInputBoxWidthAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Layout',
}

export const TextInputBoxWidth = () => {
  const [textInputBoxWidth, setTextInputBoxWidth] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {textInputBoxWidth.value}
      </p>
      <TextInputBoxWidthAttribute
        text_input_width={textInputBoxWidth}
        update={(changes) => setTextInputBoxWidth(changes)}
      />
    </>
  )
}

TextInputBoxWidth.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const input = canvas.getByTestId('text-input-box-width-attribute-input')
  const label = canvas.getByTestId('text-input-box-width-attribute-label')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we type',
    async () => {
      await userEvent.clear(input)
      await userEvent.type(input, '50')
      expect(output.innerHTML).toBe('50')
    }
  )

  await step(
    'Expect the callback value to include 100 if we type a value that is more than 100.',
    async () => {
      await userEvent.clear(input)
      await userEvent.type(input, '101')
      expect(output.innerHTML).toBe('100')
    }
  )

  await step(
    'Expect the callback value to include 100 if we type a value that is more than 100.',
    async () => {
      await userEvent.clear(input)
      await userEvent.type(input, '101')
      expect(output.innerHTML).toBe('100')
    }
  )

  await step(
    'Expect the callback value to include 15 when blurred if we type a value that is less than 15.',
    async () => {
      await userEvent.clear(input)
      await userEvent.type(input, '14')
      await userEvent.click(label)
      expect(output.innerHTML).toBe('15')
    }
  )
}
