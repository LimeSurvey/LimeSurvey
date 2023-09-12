import { Toast } from 'helpers'
import { useAppState } from 'hooks'
import { DragDropContext, Droppable } from 'react-beautiful-dnd'

export const DragAndDrop = ({
  children,
  onDragStart,
  onDragEnd,
  droppableId,
  className,
  direction = 'vertical',
}) => {
  const [isSurveyActive] = useAppState('isSurveyActive', false)

  const handleOnDragEnd = (result) => {
    onDragEnd(result)

    if (isSurveyActive) {
      Toast('Drag and drop is disabled while survey is published.')
    }
  }

  return (
    <DragDropContext onDragStart={onDragStart} onDragEnd={handleOnDragEnd}>
      <Droppable
        isDropDisabled={isSurveyActive}
        direction={direction}
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
  )
}
