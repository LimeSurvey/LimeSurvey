import { useAppState, useFocused } from 'hooks'
import classNames from 'classnames'
import { SideBar } from 'components/SideBar'
import { QuestionSettings } from 'components/QuestionSettings/QuestionSettings'
import { WelcomeSettings } from 'components/WelcomeSettings/WelcomeSettings'
import { QuestionTypeInfo } from 'components/QuestionTypes'
import { EndScreenSettings } from 'components/EndScreenSettings/EndScreenSettings'

export const RightSideBar = ({ surveyId }) => {
  const [editorSettingsPanelOpen] = useAppState('editorSettingsPanelOpen', true)
  const [settingsPanelOpen] = useAppState('settingsPanelOpen', false)

  const { focused } = useFocused()
  if (settingsPanelOpen) {
    return <></>
  }
  return (
    <SideBar
      className={classNames('right-side-bar pb-4 pt-2', {
        'active-side-bar': focused,
        'bg-white': focused,
      })}
      visible={focused ? focused : editorSettingsPanelOpen}
    >
      {focused && <QuestionSettings surveyId={surveyId} />}
      {focused?.info?.type === QuestionTypeInfo.WELCOME_SCREEN.type && (
        <WelcomeSettings surveyId={surveyId} />
      )}
      {focused?.info?.type === QuestionTypeInfo.END_SCREEN.type && (
        <EndScreenSettings surveyId={surveyId} />
      )}
    </SideBar>
  )
}
