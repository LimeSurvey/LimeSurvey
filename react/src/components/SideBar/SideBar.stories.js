import { SideBar } from './SideBar'

export default {
  title: 'General/SideBar',
  component: SideBar,
  argTypes: {
    visible: {
      name: 'Toggle visibility',
      control: { type: 'boolean' },
    },
  },
  args: {
    visible: true,
  },
}

export const Basic = {
  render: (args) => <SideBar {...args}>Sample Content</SideBar>,
}
