import { Button } from 'react-bootstrap'
import classnames from 'classnames'

import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from 'components'
import { AddIcon, CloseIcon } from 'components/icons'

export const AddQuestion = ({
  toggleDarkOnOpen = false,
  groupIndex = null,
  onClick = () => {},
  id = null,
  className = '',
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const [isAddingQuestionOrGroup, setIsAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP,
    false
  )
  const [, setClickedQuestionGroupIndex] = useAppState(
    STATES.CLICKED_QUESTION_GROUP_INDEX,
    0
  )
  const toolTip = !hasSurveyUpdatePermission
    ? getTooltipMessages().NO_PERMISSION
    : getTooltipMessages().ACTIVE_DISABLED

  return (
    <div
      className={classnames('add-question-container', className, {
        'cursor-not-allowed': !hasSurveyUpdatePermission,
      })}
      id={id}
    >
      <TooltipContainer
        placement="bottom"
        tip={toolTip}
        showTip={isSurveyActive || !hasSurveyUpdatePermission}
      >
        <Button
          disabled={isSurveyActive || !hasSurveyUpdatePermission}
          onClick={() => {
            setIsAddingQuestionOrGroup(!isAddingQuestionOrGroup)
            setClickedQuestionGroupIndex(groupIndex)
            onClick()
          }}
          variant={
            isAddingQuestionOrGroup && toggleDarkOnOpen ? 'dark' : 'primary'
          }
          className={classnames(
            'add-question-button d-flex align-items-center justify-content-center'
          )}
          data-testid="add-question-button"
        >
          {isAddingQuestionOrGroup ? (
            <CloseIcon className="text-white fill-current" />
          ) : (
            <AddIcon fill="white" />
          )}
        </Button>
      </TooltipContainer>
    </div>
  )
}
