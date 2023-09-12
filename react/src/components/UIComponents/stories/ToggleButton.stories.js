import { useState } from 'react'
// import { userEvent, within } from '@storybook/testing-library'
// import { expect } from '@storybook/jest'

import StoryWrapper from '../StoryWrapper'
import { ToggleButtons } from '../Buttons'

export default {
  title: 'Global/Button/ToggleButton',
  component: ToggleButtons,
}

const Template = (args) => {
  const [selectedValue, setSelectedValue] = useState('1')

  return (
    <StoryWrapper>
      <ToggleButtons
        name="toggle-buttons"
        value={selectedValue}
        onChange={(value) => setSelectedValue(value)}
        {...args}
      />
    </StoryWrapper>
  )
}
export const Toggle2 = Template.bind({})
Toggle2.args = {
  id: 'c2',
  labelText: 'Toggle Button With Two Options',
  onOffToggle: true,
}

// ToggleButton.play = async ({ canvasElement, step }) => {
//   const canvas = within(canvasElement)
//   const buttonsGroup = canvas.getByTestId('toggle-buttons-toggle-group')
//   const onButton = canvas.getByTestId('toggle-buttons-on-toggler')
//   const offButton = canvas.getByTestId('toggle-buttons-off-toggler')
//   const output = canvas.getByTestId('output')

//   await step('Should display exactly two input fields', async () => {
//     expect(buttonsGroup.getElementsByTagName('input').length).toBe(2)
//   })

//   await step('Should output true when clicking on the onButton', async () => {
//     await userEvent.click(onButton)
//     expect(output.innerHTML).toBe('true')
//   })

//   await step('Should output false when clicking on the onButton', async () => {
//     await userEvent.click(offButton)
//     expect(output.innerHTML).toBe('false')
//   })

//   await step(
//     'Should output true when clicking on the onButton after clicking on the offButton',
//     async () => {
//       await userEvent.click(onButton)
//       expect(output.innerHTML).toBe('true')
//     }
//   )
// }
