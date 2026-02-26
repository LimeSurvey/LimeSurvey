import { useState } from 'react'
import { ToggleButtons } from '../../Buttons/ToggleButtons'

export default {
  title: 'UIComponents/Button/ToggleButton',
  component: ToggleButtons,
}

export function Basic(args) {
  const [value, setValue] = useState(-1)
  return (
    <div data-testid="toggle-buttons-toggle-group">
      <p className="d-none" data-testid="output">
        {JSON.stringify(value)}
      </p>
      <ToggleButtons
        id="toggle-buttons"
        value={value}
        onChange={(value) => setValue(value)}
        {...args}
      />
    </div>
  )
}

// Basic.play = async ({ canvasElement, step }) => {
//   const canvas = within(canvasElement)
//   const buttonsGroup = canvas.getByTestId('toggle-buttons-toggle-group')
//   const onButton = canvas.getByTestId('toggle-buttons-on-toggler')
//   const offButton = canvas.getByTestId('toggle-buttons-off-toggler')
//   const output = canvas.getByTestId('output')

//   await step('Should display exactly two input fields', async () => {
//     await expect(buttonsGroup.getElementsByTagName('input').length).toBe(2)
//   })

//   await step('Should output true when clicking on the onButton', async () => {
//     await userEvent.click(onButton)
//     await expect(output.innerHTML).toBe('true')
//     await sleep()
//   })

//   await step('Should output false when clicking on the onButton', async () => {
//     await userEvent.click(offButton)
//     await expect(output.innerHTML).toBe('false')
//     await sleep()
//   })

//   await step(
//     'Should output true when clicking on the onButton after clicking on the offButton',
//     async () => {
//       await userEvent.click(onButton)
//       await expect(output.innerHTML).toBe('true')
//       await sleep()
//     }
//   )
// }

// export function WithThreeOptions(args) {
//   const [value, setValue] = useState(-1)

//   return (
//       <div data-testid="toggle-buttons-toggle-group-three-options">
//         <p className=" d-none" data-testid="output">
//           {JSON.stringify(value)}
//         </p>
//         <ToggleButtons
//           id="toggle-buttons"
//           labelText="Toggle Button With Three Options"
//           value={value}
//           onOffToggle={false}
//           onChange={(value) => setValue(value)}
//           {...args}
//         />
//       </div>
//   )
// }
// WithThreeOptions.play = async ({ canvasElement, step }) => {
//   const canvas = within(canvasElement)
//   const buttonsGroup = canvas.getByTestId(
//     'toggle-buttons-toggle-group-three-options'
//   )
//   const onButton = canvas.getByTestId('toggle-buttons-on-toggler')
//   const offButton = canvas.getByTestId('toggle-buttons-off-toggler')
//   const output = canvas.getByTestId('output')

//   await step('Should display exactly two input fields', async () => {
//     await expect(buttonsGroup.getElementsByTagName('input').length).toBe(3)
//   })

//   await step('Should output true when clicking on the onButton', async () => {
//     await userEvent.click(onButton)
//     await expect(output.innerHTML).toBe('"1"')
//     await sleep()
//   })

//   await step('Should output false when clicking on the onButton', async () => {
//     await userEvent.click(offButton)
//     await expect(output.innerHTML).toBe('"0"')
//     await sleep()
//   })
// }
