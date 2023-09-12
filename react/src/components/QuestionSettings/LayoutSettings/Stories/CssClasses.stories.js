import { useState } from 'react'
import { expect } from '@storybook/jest'
import { userEvent, within } from '@storybook/testing-library'

import { CssClasses as CssClassesAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Layout',
}

export const CssClasses = () => {
  const [cssClass, setCssClass] = useState({ value: '' })

  return (
    <>
      <p className="d-none" data-testid="output">
        {cssClass.value}
      </p>
      <CssClassesAttribute
        cssclass={cssClass}
        update={(changes) => {
          setCssClass(changes)
        }}
      />
    </>
  )
}

CssClasses.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const input = canvas.getByTestId('css-classes-attribute-input')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we type',
    async () => {
      await userEvent.type(input, 'random CSS class(es)')
      expect(output.innerHTML).toBe('random CSS class(es)')
      await userEvent.clear(input)
    }
  )
}
