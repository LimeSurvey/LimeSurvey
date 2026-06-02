import { Editor as EditorComponent } from './Editor'

const meta = {
  title: 'Page/Editor',
  component: EditorComponent,
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
  args: {
    structurePanel: true,
    settingsPanel: true,
  },
}

export default meta

export const Editor = () => {
  return <EditorComponent />
}
