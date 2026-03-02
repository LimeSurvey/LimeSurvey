import { fireEvent, waitFor, within, expect } from '@storybook/test'
import { Draggable } from 'react-beautiful-dnd'
import { useState } from 'react'

import { sleep } from 'helpers/sleep'
import { DragIcon } from 'components/icons'
import { DragAndDrop as DragAndDropComponent } from '../DragAndDrop/DragAndDrop'

export default {
  title: 'UIComponents/DragAndDrop',
  component: DragAndDropComponent,
}

const DATA = [
  { value: 'A', qid: '1', aid: 'a' },
  { value: 'B', qid: '2', aid: 'b' },
  { value: 'C', qid: '3', aid: 'c' },
  { value: 'D', qid: '4', aid: 'd' },
]

function arrayMove(arr, fromIndex, toIndex) {
  const arrayData = [...arr]
  const element = arr[fromIndex]
  arrayData.splice(fromIndex, 1)
  arrayData.splice(toIndex, 0, element)
  return arrayData
}

export const DragAndDrop = () => {
  const [testData, setTestData] = useState(DATA)
  const handleOnDragEnd = (result) => {
    const { source, destination } = result
    if (!destination) {
      return
    }
    if (source.index === destination.index) {
      return
    }
    const updatedArray = arrayMove(testData, source.index, destination.index)
    setTestData([...updatedArray])
  }

  return (
    <DragAndDropComponent
      onDragEnd={handleOnDragEnd}
      droppableId={'droppable'}
      onDragStart={() => ({})}
      dataTestId="drag-and-drop"
    >
      {testData.map((answer, index) => (
        <Draggable
          key={`ranking-${answer.qid}-${answer.aid}`}
          draggableId={`ranking-${answer.qid}-${answer.aid}`}
          index={index}
        >
          {(provided) => (
            <div
              ref={provided.innerRef}
              {...provided.draggableProps}
              className=" d-flex"
            >
              <div {...provided.dragHandleProps}>
                <DragIcon className="text-secondary fill-current me-2" />
              </div>
              {answer.value}
            </div>
          )}
        </Draggable>
      ))}
    </DragAndDropComponent>
  )
}

DragAndDrop.play = async ({ canvasElement, step }) => {
  const { getByTestId } = within(canvasElement)
  await waitFor(() => getByTestId('drag-and-drop'))
  const container = getByTestId('drag-and-drop')
  const elements = container.firstChild.children

  await step('Should render DragAndDrop correctly', async () => {
    await expect(container).toBeInTheDocument()
    await expect(elements.length).toBe(DATA.length)
  })

  await step('Should drag "A" to the last position', async () => {
    fireEvent.dragStart(elements[0], {
      clientY: 20 * 4,
    })
    await waitFor(() => {
      expect(elements[0].innerText).toBe('A')
    })
    await sleep(500)
  })
}
