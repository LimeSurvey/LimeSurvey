import classNames from 'classnames'
import { Draggable } from 'react-beautiful-dnd'
import { Button, Form } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'

import { RandomNumber } from 'helpers'
import { DragAndDrop } from 'components/UIComponents/DragAndDrop/DragAndDrop'

import { MultipleChoiceAnswer } from './MultipleChoiceAnswer'
import { QuestionTypeInfo } from '../QuestionTypeInfo'
import { useEffect, useRef, useState } from 'react'

export const MultipleChoice = ({
  question: { answers, qid, questionThemeName, attributes = {} } = {},
  handleUpdate = () => {},
  showCommentsInputs = false,
  isFocused = false,
}) => {
  const [highestWidth, setHighestWidth] = useState(0)

  const containersRef = useRef(null)
  const handleFocus = () => {}
  const handleBlur = () => {}

  const isButtonsTheme =
    questionThemeName === QuestionTypeInfo.MULTIPLE_CHOICE_BUTTONS.theme

  useEffect(() => {
    if (isFocused || !containersRef.current) {
      setHighestWidth('fit-content')
      return
    }

    const heightObserver = new ResizeObserver(() => {
      if (!containersRef.current) {
        return
      }

      let highestWidth = 0
      if (!containersRef.current) {
        return
      }
      containersRef.current
        .querySelectorAll('.multi-choice-form-label')
        .forEach((item) => {
          if (item.clientWidth > highestWidth) {
            highestWidth = item.clientWidth
          }
        })
      setHighestWidth(highestWidth ? highestWidth : 'fit-content')
    })

    containersRef.current
      .querySelectorAll('.multi-choice-form-label')
      .forEach((item) => {
        heightObserver.observe(item)
      })

    heightObserver.observe(containersRef.current)

    return () => {
      heightObserver.disconnect()
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [containersRef.current, isFocused])

  const handleUpdateAnswer = (newAnswerValue, index, isComment) => {
    const updatedQuestionAnswers = [...answers]
    if (isComment) {
      updatedQuestionAnswers[index].assessmentComment = newAnswerValue
    } else {
      updatedQuestionAnswers[index].assessmentValue = newAnswerValue
    }

    handleUpdate({ answers: updatedQuestionAnswers })
  }

  const handleAddingAnswers = () => {
    const updatedQuestionAnswers = [...answers]
    const lastAnswerId = answers[answers.length - 1]?.aid || 0

    const newAnswer = {
      aid: lastAnswerId + 1 + RandomNumber(),
      qid: qid,
      code: `A${updatedQuestionAnswers.length}`,
      assessmentValue: '',
      sortorder: updatedQuestionAnswers.length,
      scaleId: 0,
    }

    updatedQuestionAnswers.push(newAnswer)
    handleUpdate({ answers: updatedQuestionAnswers })
  }

  const handleRemovingAnswers = (answerId) => {
    const updatedQuestionAnswers = answers.filter(
      (answer) => answer.aid !== answerId
    )

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

  return (
    <div className="multiple-choice-question d-flex w-100">
      <div
        style={{
          width: '100%',
        }}
      >
        <Form ref={containersRef} className={classNames('py-2 ', {})}>
          <DragAndDrop
            onDragEnd={handleOnDragEnd}
            droppableId={'droppable'}
            className={classNames('answers-list', {
              'd-flex flex-wrap': !isFocused && isButtonsTheme,
              'flex-column': isButtonsTheme,
            })}
          >
            {answers.map((answer, index) => (
              <Draggable
                key={`${answer.qid}-${answer.aid}`}
                draggableId={`${answer.qid}-${answer.aid}`}
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
                        'mb-2': !isButtonsTheme,
                      },
                      'question-body-content mb-3'
                    )}
                  >
                    <MultipleChoiceAnswer
                      provided={provided}
                      answer={answer}
                      index={index}
                      isFocused={isFocused}
                      handleUpdateAnswer={handleUpdateAnswer}
                      handleFocus={handleFocus}
                      handleBlur={handleBlur}
                      handleRemovingAnswers={handleRemovingAnswers}
                      showCommentsInputs={showCommentsInputs}
                      questionThemeName={questionThemeName}
                      highestWidth={highestWidth}
                      attributes={attributes}
                    />
                  </div>
                )}
              </Draggable>
            ))}
          </DragAndDrop>
        </Form>
        <div>
          <Button
            onClick={handleAddingAnswers}
            variant={'outline'}
            className={classNames(
              'text-primary d-flex align-items-center gap-2 p-0',
              {
                'd-none disabled': !isFocused,
              }
            )}
            data-testid="add-answer-button"
          >
            <PlusLg /> Add choice
          </Button>
        </div>
      </div>
    </div>
  )
}
