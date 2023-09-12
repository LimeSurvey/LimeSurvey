import { useState } from 'react'

import { StoryWrapper } from '../StoryWrapper'
import { AlignButtons } from '../Buttons'

export default {
  title: 'Global/Button/AlignButton',
  component: AlignButtons,
}

const Template = (args) => {
  const [selectedValue, setSelectedValue] = useState(1)

  return (
    <StoryWrapper>
      <AlignButtons
        name="Align-buttons"
        value={selectedValue}
        onChange={(value) => setSelectedValue(value)}
        {...args}
      />
    </StoryWrapper>
  )
}
export const AlignButton = Template.bind({})
AlignButton.args = {
  labelText: 'This is Align Button',
}
