import { useState } from 'react'
// import { userEvent, within } from '@storybook/testing-library'
// import { expect } from '@storybook/jest'

import InputComponent from '../Input/Input'
import StoryWrapper from '../StoryWrapper'

export default {
  title: 'Global/Input',
  component: InputComponent,
}

const Template = (args) => {
  const [value, setValue] = useState('')

  return (
    <StoryWrapper>
      <InputComponent
        labelText="Label Text"
        dataTestId="story-input-test-id"
        placeholder="default input"
        value={value}
        onChange={({ target: { value } }) => setValue(value)}
        {...args}
      />
    </StoryWrapper>
  )
}
export const Input = Template.bind({})

Input.args = {
  type: 'text',
}

// Input.play = async ({ canvasElement, step }) => {
//   const canvas = within(canvasElement)
//   const input = canvas.getByTestId('story-input-test-id')

//   await step('should have the value "random text 123"', async () => {
//     userEvent.type(input, 'random text 123')
//     expect(input.value).toBe('random text 123')
//   })

//   await step('should be able to clear the text', async () => {
//     userEvent.clear(input)
//     expect(input.value).toBe('')
//   })
// }

// export const NumbersOnlyInput = () => {
//   const [value, setValue] = useState('')

//   return (
//     <InputComponent
//       dataTestId={'story-input-test-id'}
//       placeholder="story input"
//       value={value}
//       onChange={({ target: { value } }) => setValue(value)}
//       type="number"
//     />
//   )
// }

// NumbersOnlyInput.play = async ({ canvasElement, step }) => {
//   const canvas = within(canvasElement)
//   const input = canvas.getByTestId('story-input-test-id')

//   await step('should have the value "123"', async () => {
//     await userEvent.type(input, '123')
//     expect(input.value).toBe('123')
//   })

//   await step('should be able to clear the text', async () => {
//     await userEvent.clear(input)
//     expect(input.value).toBe('')
//   })

//   await step(
//     'should not be able to use the characters "+, -, e, E"',
//     async () => {
//       await userEvent.type(input, '+-eE')
//       expect(input.value).toBe('')
//     }
//   )

//   await step(
//     'should have the numbers "43" after typing "r4ndom t3xt"',
//     async () => {
//       await userEvent.type(input, 'r4ndom t3xt')
//       expect(input.value).toBe('43')
//     }
//   )
// }
