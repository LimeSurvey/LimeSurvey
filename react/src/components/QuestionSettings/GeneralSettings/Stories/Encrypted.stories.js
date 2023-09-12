import { useState } from 'react'
import { Encrypted as EncryptedAttribute } from '../Attributes'
import { userEvent, within } from '@storybook/testing-library'
import { expect } from '@storybook/jest'

export default {
  title: 'QuestionAttributes/Basic',
}

export const Encrypted = () => {
  const [encrypted, setEncrypted] = useState({ encrypted: false })

  return (
    <>
      <p className="d-none" data-testid="output">
        {encrypted.encrypted === true && 'true'}
        {encrypted.encrypted === false && 'false'}
      </p>
      <EncryptedAttribute
        isEncrypted={encrypted.encrypted}
        update={(value) => setEncrypted(value)}
      />
    </>
  )
}

Encrypted.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  const onBtn = canvas.getByTestId('encrypted-question-settings-on-toggler')
  const offBtn = canvas.getByTestId('encrypted-question-settings-off-toggler')
  const output = canvas.getByTestId('output')

  await step(
    'Expect the callback output to include an encrypted property when we click on the on button with value "true"',
    async () => {
      await userEvent.click(onBtn)
      expect(output.innerHTML).toBe('true')
    }
  )

  await step(
    'Expect the callback output to include an encrypted property when we click on the on button with value "false"',
    async () => {
      await userEvent.click(offBtn)
      expect(output.innerHTML).toBe('false')
    }
  )
}
