import DropZoneComponent from '../DropZone/DropZone'
import { StoryWrapper } from '../StoryWrapper'

export default {
  title: 'Global/DropZone',
  component: DropZoneComponent,
}

const Template = (args) => {
  return (
    <StoryWrapper>
      <DropZoneComponent {...args} />
    </StoryWrapper>
  )
}
export const DropZone = Template.bind({})
