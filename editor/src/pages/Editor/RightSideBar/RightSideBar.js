import { useAppState, useFocused } from 'hooks'
import classNames from 'classnames'
import { useParams } from 'react-router-dom'

import { STATES } from 'helpers'
import { getSurveyPanels } from 'helpers/options'
import { QuestionSettings } from 'components/QuestionSettings/QuestionSettings'
import { WelcomeSettings } from 'components/WelcomeSettings/WelcomeSettings'
import { GroupSettings } from 'components/GroupSettings/GroupSettings'
import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { EndScreenSettings } from 'components/EndScreenSettings/EndScreenSettings'

export const RightSideBar = ({ surveyId }) => {
  const { panel } = useParams()
  const [isAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP,
    false
  )
  const { focused, groupIndex, questionIndex } = useFocused()
  const shouldDisplay = focused && Object.keys(focused).length > 0

  if (
    isAddingQuestionOrGroup ||
    (panel && panel !== getSurveyPanels().structure.panel)
  ) {
    return <></>
  }

  return (
    <div
      className={classNames('right-side-bar', {
        'bg-white sidebar active-side-bar': shouldDisplay,
        'd-none': !shouldDisplay,
      })}
    >
      {focused && groupIndex != null && questionIndex == null && (
        <GroupSettings gid={focused.gid} />
      )}
      {focused && <QuestionSettings surveyId={surveyId} />}
      {focused?.info?.type === getQuestionTypeInfo().WELCOME_SCREEN.type && (
        <WelcomeSettings surveyId={surveyId} />
      )}
      {focused?.info?.type === getQuestionTypeInfo().END_SCREEN.type && (
        <EndScreenSettings surveyId={surveyId} />
      )}
    </div>
  )
}
