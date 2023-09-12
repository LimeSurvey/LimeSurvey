import { Button } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'
import classNames from 'classnames'
import { RankingQuestionAnswer } from './RankingQuestionAnswer'
import { DragAndDrop } from 'components/UIComponents'
import { Draggable } from 'react-beautiful-dnd'

export const RankingQuestionAnswers = ({
  question: { answers = [] } = {},
  handleUpdate = () => {},
  isFocused,
  handleAddingAnswers,
}) => {
  const handleUpdateAnswer = (newAnswerValue, index) => {
    const updatedQuestionAnswers = [...answers]
    updatedQuestionAnswers[index].assessmentValue = newAnswerValue

    handleUpdate({ answers: updatedQuestionAnswers })
  }

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: `0 0 8px 0`,
    ...draggableStyle,
  })

  const handleOnDragEnd = (dropResult) => {
    // setIsReorderingAnswers(false)

    // dropped outside the list
    if (!dropResult.destination) {
      return
    }

    const updatedQuestionAnswers = reorderQuestionAnswers(
      answers,
      dropResult.source.index,
      dropResult.destination.index
    )

    handleUpdate({ answers: updatedQuestionAnswers })
  }

  const reorderQuestionAnswers = (listRadioAnswers, startIndex, endIndex) => {
    const updatedList = [...listRadioAnswers]
    const [removed] = updatedList.splice(startIndex, 1)
    updatedList.splice(endIndex, 0, removed)

    return updatedList.map((answer, index) => {
      answer.sortorder = index + 1
      return answer
    })
  }

  const handleRemovingAnswers = (answerId) => {
    const updatedQuestionAnswers = answers.filter(
      (answer) => answer.aid !== answerId
    )

    handleUpdate({ answers: updatedQuestionAnswers })
  }

  return (
    <>
      {isFocused && (
        <div className="mb-4">
          <h5>You can add texts or upload images</h5>
        </div>
      )}
      <DragAndDrop onDragEnd={handleOnDragEnd} droppableId={'droppable'}>
        {answers.map((answer, index) => (
          <Draggable
            key={`ranking-${answer.qid}-${answer.aid}`}
            draggableId={`ranking-${answer.qid}-${answer.aid}`}
            index={index}
          >
            {(provided, snapshot) => (
              <div
                ref={provided.innerRef}
                {...provided.draggableProps}
                style={getAnswerStyle(provided.draggableProps.style)}
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
                  onChange={(value) => handleUpdateAnswer(value, index)}
                  provided={provided}
                  handleRemovingAnswers={handleRemovingAnswers}
                />
              </div>
            )}
          </Draggable>
        ))}
      </DragAndDrop>
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
