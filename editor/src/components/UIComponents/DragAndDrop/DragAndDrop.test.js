// Import shared mocks
import 'tests/mocks'

import { DragAndDrop } from './DragAndDrop'
import { renderWithProviders } from 'tests/testUtils'
import { screen } from '@testing-library/react'
import { useState } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import { DragDropContext } from 'react-beautiful-dnd'
import { DragIcon } from 'components/icons'

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

describe('DragAndDrop', () => {
  test('Should render DragAndDrop correctly', async () => {
    const DragAndDropWrapper = () => {
      const [testData, setTestData] = useState(DATA)
      const handleOnDragEnd = (result) => {
        const { source, destination } = result
        if (!destination) {
          return
        }
        if (source.index === destination.index) {
          return
        }
        const updatedArray = arrayMove(
          testData,
          source.index,
          destination.index
        )
        setTestData([...updatedArray])
      }

      return (
        <DragDropContext onDragEnd={handleOnDragEnd}>
          <DragAndDrop
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
          </DragAndDrop>
        </DragDropContext>
      )
    }

    await renderWithProviders(<DragAndDropWrapper />)
    const container = screen.getByTestId('drag-and-drop')
    const elements = container.firstChild.children

    expect(container).toBeInTheDocument()
    expect(elements.length).toBe(DATA.length)
  })
})
