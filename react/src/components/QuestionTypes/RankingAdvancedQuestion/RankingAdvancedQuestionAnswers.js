import { Button } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'

import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd'
import { RankingQuestionAnswer } from '../RankingQuestion/RankingQuestionAnswer'

const reorder = (list, startIndex, endIndex) => {
  const result = Array.from(list)
  const [removed] = result.splice(startIndex, 1)
  result.splice(endIndex, 0, removed)

  return result
}

const move = (source, destination, droppableSource, droppableDestination) => {
  const sourceClone = Array.from(source)
  const destClone = Array.from(destination)
  const [removed] = sourceClone.splice(droppableSource.index, 1)

  destClone.splice(droppableDestination.index, 0, removed)

  const result = {}
  result[droppableSource.droppableId] = sourceClone
  result[droppableDestination.droppableId] = destClone

  return result
}
const grid = 8

const getListStyle = (isDraggingOver) => ({
  background: isDraggingOver ? 'lightblue' : '#fff',
  padding: grid,
  width: '50%',
})

export const RankingAdvancedQuestionAnswers = ({
  question: { firstAnswers, secondAnswers } = {},
  handleUpdate = () => {},
  isFocused,
  handleAddingAnswers,
}) => {
  function onDragEnd(result) {
    const { source, destination } = result

    // dropped outside the list
    if (!destination) {
      return
    }
    const sInd = +source.droppableId
    const dInd = +destination.droppableId

    if (sInd === dInd) {
      const items = reorder(
        [firstAnswers || [], secondAnswers || []][sInd],
        source.index,
        destination.index
      )
      const newState = [...[firstAnswers || [], secondAnswers || []]]
      newState[sInd] = items
      // setState(newState)
      handleUpdate({ firstAnswers: newState[0], secondAnswers: newState[1] })
    } else {
      const result = move(
        [firstAnswers || [], secondAnswers || []][sInd],
        [firstAnswers || [], secondAnswers || []][dInd],
        source,
        destination
      )
      const newState = [...[firstAnswers || [], secondAnswers || []]]

      newState[sInd] = result[sInd]
      newState[dInd] = result[dInd]
      console.log('newState: ', newState)
      handleUpdate({
        firstAnswers: newState[0],
        secondAnswers: newState[1],
      })

      // setState(newState.filter((group) => group.length))
    }
  }

  const handleUpdateAnswer = (newAnswerValue, index, ind) => {
    const answers =
      ind === 0 ? [...(firstAnswers || [])] : [...(secondAnswers || [])]
    answers[index].assessmentValue = newAnswerValue
    if (ind === 0) {
      handleUpdate({ firstAnswers: answers })
    } else {
      handleUpdate({
        secondAnswers: answers,
      })
    }
  }

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: `0 0 8px 0`,
    ...draggableStyle,
  })

  const handleRemovingAnswers = (answerId, gid) => {
    let answers = gid === 0 ? firstAnswers : secondAnswers
    const updatedQuestionAnswers = answers.filter(
      (answer) => answer.aid !== answerId
    )
    answers = [...updatedQuestionAnswers]
    if (gid === 0) {
      handleUpdate({ firstAnswers: answers })
    } else {
      handleUpdate({
        secondAnswers: answers,
      })
    }
  }

  return (
    <>
      {isFocused && (
        <div className="mb-4">
          <h5>You can add texts or upload images</h5>
        </div>
      )}
      <div style={{ display: 'flex' }}>
        <DragDropContext onDragEnd={onDragEnd}>
          {[firstAnswers || [], secondAnswers || []].map(
            (groupAnswers, ind) => (
              <Droppable key={ind} droppableId={`${ind}`}>
                {(provided, snapshot) => (
                  <div
                    ref={provided.innerRef}
                    style={getListStyle(snapshot.isDraggingOver)}
                    {...provided.droppableProps}
                    className="border text-overflow-ellipsis"
                  >
                    <div className="my-2">
                      {ind === 0 ? 'Available Items' : 'Your Ranking'}
                    </div>

                    {groupAnswers.map((answer, index) => (
                      <Draggable
                        key={`ranking-${answer.qid}-${answer.aid}`}
                        draggableId={`ranking-${answer.qid}-${answer.aid}`}
                        index={index}
                      >
                        {(provided, snapshot) => (
                          <div
                            ref={provided.innerRef}
                            {...provided.draggableProps}
                            style={getAnswerStyle(
                              provided.draggableProps.style
                            )}
                            className={classNames(
                              {
                                'focus-element': snapshot.isDragging,
                              },
                              'mb-2'
                            )}
                          >
                            <RankingQuestionAnswer
                              answer={answer}
                              isFocused={isFocused}
                              index={index}
                              onChange={(value) =>
                                handleUpdateAnswer(value, index, ind)
                              }
                              provided={provided}
                              handleRemovingAnswers={(id) =>
                                handleRemovingAnswers(id, ind)
                              }
                            />
                          </div>
                        )}
                      </Draggable>
                    ))}
                  </div>
                )}
              </Droppable>
            )
          )}
        </DragDropContext>
      </div>

      <div>
        <Button
          onClick={handleAddingAnswers}
          variant={'outline'}
          className={classNames(
            'text-primary add-radio-question-answer-button px-0 mt-2',
            {
              'd-none disabled': !isFocused,
            }
          )}
          data-testid="single-choice-add-answer-button"
        >
          <PlusLg /> Add choice
        </Button>
      </div>
    </>
  )
}
