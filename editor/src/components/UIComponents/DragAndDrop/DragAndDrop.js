import { DragDropContext, Droppable } from 'react-beautiful-dnd'

import { useAppState } from '../../../hooks'
import { errorToast, STATES } from '../../../helpers'
import { getTooltipMessages } from 'helpers/options'

export const DragAndDrop = ({
  children,
  onDragStart,
  onDragEnd,
  droppableId,
  className,
  direction = 'vertical',
  dataTestId = 'drag-and-drop',
  isDropDisabled = false,
  surveyActiveDisable = true,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const disabled =
    (isSurveyActive && surveyActiveDisable) ||
    isDropDisabled ||
    !hasSurveyUpdatePermission

  const handleOnDragEnd = (result) => {
    if ((isSurveyActive && surveyActiveDisable) || !hasSurveyUpdatePermission)
      errorToast(
        isSurveyActive && surveyActiveDisable
          ? getTooltipMessages().ACTIVE_DISABLED
          : getTooltipMessages().NO_PERMISSION
      )
    onDragEnd(result)
  }

  return (
    <div data-testid={dataTestId}>
      <DragDropContext onDragStart={onDragStart} onDragEnd={handleOnDragEnd}>
        <Droppable
          isDropDisabled={disabled}
          direction={direction}
          key={droppableId}
          droppableId={droppableId}
        >
          {(provided) => (
            <div
              className={className}
              ref={provided.innerRef}
              {...provided.droppableProps}
              {...provided.droppableProps}
            >
              {children}
              {provided.placeholder}
            </div>
          )}
        </Droppable>
      </DragDropContext>
    </div>
  )
}
