import { LeftSideBar as LeftSideBarComponent } from './LeftSideBar'

const meta = {
  title: 'Page/Editor/LeftSideBar',
  component: LeftSideBarComponent,
  argTypes: {
    structurePanel: {
      name: 'Toggle structure panel',
      control: { type: 'boolean' },
    },
    settingsPanel: {
      name: 'Toggle settings panel',
      control: { type: 'boolean' },
    },
  },
}

export default meta

export const LeftSideBar = () => {
  return <LeftSideBarComponent />
}
