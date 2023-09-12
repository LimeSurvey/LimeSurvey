import { useState } from 'react'
import { expect } from '@storybook/jest'
import { userEvent, within } from '@storybook/testing-library'

import { HideQuestion as HideQuestionAttribute } from '../Attributes'

export default {
  title: 'QuestionAttributes/Layout',
}

export const HideQuestion = () => {
  const [hideQuestion, setHideQuestion] = useState({ value: false })

  return (
    <>
      <p className="d-none" data-testid="output">
        {hideQuestion.value === true && 'true'}
        {hideQuestion.value === false && 'false'}
      </p>
      <HideQuestionAttribute
        hide_question={hideQuestion}
        update={(changes) => setHideQuestion(changes)}
      />
    </>
  )
}

HideQuestion.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const onBtn = canvas.getByTestId('hide-question-question-settings-on-toggler')
  const offBtn = canvas.getByTestId(
    'hide-question-question-settings-off-toggler'
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
    'Expect the callback output to include a value property when we click on the on button with value "false"',
    async () => {
      await userEvent.click(offBtn)
      expect(output.innerHTML).toBe('false')
    }
  )
}
