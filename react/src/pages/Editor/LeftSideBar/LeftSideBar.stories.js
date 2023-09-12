import { useEffect } from 'react'

import { useAppState } from 'hooks/useAppState'

import { LeftSideBar } from './LeftSideBar'

const ComponentWithStateArgs = ({
  structurePanel = true,
  settingsPanel = true,
}) => {
  const [, toggleStructurePanel] = useAppState('editorStructurePanelOpen')
  const [, toggleSettingsPanel] = useAppState('editorSettingsPanelOpen')

  useEffect(() => {
    if (toggleStructurePanel) toggleStructurePanel(structurePanel)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [structurePanel])

  useEffect(() => {
    if (toggleSettingsPanel) toggleSettingsPanel(settingsPanel)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [settingsPanel])

  return <LeftSideBar />
}

const meta = {
  title: 'Page/Editor/LeftSideBar',
  component: ComponentWithStateArgs,
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

export const Basic = {
  render: (args) => <ComponentWithStateArgs {...args} />,
}
