import { useEffect, useRef, useState } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import classNames from 'classnames'

import { DragAndDrop } from 'components'
import { ArrayQuestionAnswersTitle } from './ArrayQuestionAnswerTitle'

export const ArrayQuestionAnswersTitles = ({
  question: {
    answers: _answers = [],
    qid,
    attributes: { form_field_text = {} } = {},
  } = {},
  handleUpdate,
  onDragEndCallback,
  highestSubquestionWidth,
  isFocused,
  arrayAnswersWidth,
  setArrayAnswersWidth,
  dragIconSize,
  isArrayDualScale = false,
  scaleId,
  highestHeight,
  setHighestHeight = () => {},
}) => {
  const answers = isArrayDualScale ? _answers[scaleId] : _answers

  const [isReorderingAnswers, setIsReorderingAnswers] = useState(false)
  const answersContainerRef = useRef(null)

  const handleSizeUpdate = () => {
    let highestHeight = 0
    if (!answersContainerRef.current) {
      return
    }

    answersContainerRef.current
      .querySelectorAll('.array-answer-content-editor')
      .forEach((item, index) => {
        const height = item.clientHeight
        arrayAnswersWidth[index] = item.clientWidth
        if (height > highestHeight) {
          highestHeight = height
        }
      })

    setArrayAnswersWidth([...arrayAnswersWidth])
    setHighestHeight(highestHeight, scaleId)
  }

  const handleOnDragEnd = (dragResult) => {
    setIsReorderingAnswers(false)

    onDragEndCallback(dragResult, isArrayDualScale ? scaleId : undefined)

    setTimeout(() => {
      handleSizeUpdate()
    }, 1)
  }

  const handleAnswerTitleUpdate = (newAnswerValue, index) => {
    const updatedQuestionAnswers = [...answers]
    updatedQuestionAnswers[index].assessmentValue = newAnswerValue
    if (isArrayDualScale) {
      handleUpdate({
        answers: { ..._answers, [scaleId]: updatedQuestionAnswers },
      })
    } else {
      handleUpdate({ answers: updatedQuestionAnswers })
    }
  }

  const handleRemovingAnswer = (index) => {
    const updatedQuestionAnswers = [...answers]
    updatedQuestionAnswers.splice(index, 1)

    for (let i = index; i < updatedQuestionAnswers.length; i++) {
      updatedQuestionAnswers[i].sortorder--
    }

    if (isArrayDualScale) {
      handleUpdate({
        answers: { ..._answers, [scaleId]: updatedQuestionAnswers },
      })
    } else {
      handleUpdate({ answers: updatedQuestionAnswers })
    }
  }

  useEffect(() => {
    const heightObserver = new ResizeObserver(() => {
      handleSizeUpdate()
    })

    if (answersContainerRef.current) {
      answersContainerRef.current
        .querySelectorAll('.array-answer-content-editor')
        .forEach((item) => {
          heightObserver.observe(item)
        })

      heightObserver.observe(answersContainerRef.current)
    }

    return () => {
      heightObserver.disconnect()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    ...draggableStyle,
  })

  return (
    <div
      ref={answersContainerRef}
      className={classNames({ 'ms-5': isFocused })}
    >
      <DragAndDrop
        onDragEnd={handleOnDragEnd}
        onDragStart={() => {
          setIsReorderingAnswers(true)
        }}
        droppableId={'droppable-subquestions-answers'}
        className={classNames('d-flex')}
        direction="horizontal"
      >
        <div
          style={{
            minWidth:
              !isArrayDualScale || scaleId === 'scale1'
                ? highestSubquestionWidth + dragIconSize
                : 0,
          }}
        ></div>
        {answers?.map((answer, index) => {
          return (
            <Draggable
              key={`${answer.aid}-${qid}-${index}-subQuestionAnswerTitle`}
              draggableId={`${answer.aid}-${qid}-${index}-subQuestionAnswerTitle`}
              index={index}
            >
              {(provided, snapshot) => (
                <div
                  ref={provided.innerRef}
                  {...provided.draggableProps}
                  style={getAnswerStyle(provided.draggableProps.style)}
                  className={classNames('text-center text-secondary', {
                    'focus-element': snapshot.isDragging || isReorderingAnswers,
                    'bg-grey': !(index % 2),
                  })}
                >
                  <ArrayQuestionAnswersTitle
                    handleAnswerTitleUpdate={handleAnswerTitleUpdate}
                    answer={answer}
                    dragIconSize={dragIconSize}
                    handleRemovingAnswer={handleRemovingAnswer}
                    highestHeight={highestHeight}
                    index={index}
                    isFocused={isFocused}
                    provided={provided}
                    formFieldText={form_field_text}
                  />
                </div>
              )}
            </Draggable>
          )
        })}
      </DragAndDrop>
    </div>
  )
}
