import { useAppState } from 'hooks'

import { SurveySettings } from '.'

const meta = {
  title: 'General/SurveySettings',
  component: SurveySettings,
}

export default meta

export const Basic = () => {
  const [editorSettingsPanelOpen, setEditorSettingsPanelOpen] = useAppState(
    'editorSettingsPanelOpen',
    true
  )

  return <SurveySettings />
}
