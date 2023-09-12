import { useState } from 'react'
import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

import { SaveAsDefault as SaveAsDefaultAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Basic',
}

export const SaveAsDefault = () => {
  const [saveAsDefault, setSaveAsDefault] = useState({ value: false })

  return (
    <>
      <p className="d-none" data-testid="output">
        {saveAsDefault.value === true && 'true'}
        {saveAsDefault.value === false && 'false'}
      </p>
      <SaveAsDefaultAttribute
        save_as_default={saveAsDefault}
        update={(value) => setSaveAsDefault(value)}
      />
    </>
  )
}

SaveAsDefault.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const onBtn = canvas.getByTestId(
    'save-as-default-question-settings-on-toggler'
  )
  const offBtn = canvas.getByTestId(
    'save-as-default-question-settings-off-toggler'
  )
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include a value property when we click on the on button with value "true"',
    async () => {
      await userEvent.click(onBtn)
      expect(output.innerHTML).toBe('true')
    }
  )

  await step(
    'Expect the callback output to include a value property when we click on the off button with value "false"',
    async () => {
      await userEvent.click(offBtn)
      expect(output.innerHTML).toBe('false')
    }
  )
}
