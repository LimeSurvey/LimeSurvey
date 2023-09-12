import { useEffect, useRef } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import classNames from 'classnames'

import { DragAndDrop } from 'components/UIComponents/DragAndDrop/DragAndDrop'
import { ArrayQuestionSubQuestion } from './ArrayQuestionSubQuestion'

export const ArrayQuestionSubQuestions = ({
  onDragEndCallback,
  question: {
    qid,
    questionThemeName,
    answers = [],
    subquestions = [],
    attributes: { form_field_text = {} } = {},
  } = {},
  language,
  handleUpdate,
  isFocused,
  highestWidth,
  setHighestWidth,
  arrayAnswersWidth,
  dragIconSize,
  showContentEditor = true,
  scaleId,
  subQuestionsHeight,
  setSubQuestionsHeight,
}) => {
  const subQuestionsContainerRef = useRef(null)

  const getAnswerStyle = (draggableStyle) => ({
    userSelect: 'none',
    minWidth: 'fit-content',
    ...draggableStyle,
  })

  const handleUpdateL10ns = (updated, index) => {
    const updatedSubQuestions = [...subquestions]
    let updateL10ns = { ...updatedSubQuestions[index].l10ns }

    updateL10ns[language] = {
      ...updateL10ns[language],
      ...updated,
    }

    updatedSubQuestions[index].l10ns = updateL10ns

    handleUpdate({ subquestions: updatedSubQuestions })
  }

  const handleRemovingSubQuestion = (index) => {
    const updatedSubQuestions = [...subquestions]
    updatedSubQuestions.splice(index, 1)

    for (let i = index; i < updatedSubQuestions.length; i++) {
      updatedSubQuestions[i].sortorder--
    }

    handleUpdate({ subquestions: updatedSubQuestions })
  }

  useEffect(() => {
    if (!setSubQuestionsHeight) {
      return
    }

    const observer = new ResizeObserver(() => {
      // minimum column width is 90
      let highestWidth = 90
      if (!subQuestionsContainerRef.current) {
        return
      }

      subQuestionsContainerRef.current
        .querySelectorAll('.array-subquestion-content-editor')
        .forEach((item, index) => {
          const width = item.clientWidth
          subQuestionsHeight[index] = item.clientHeight
          if (width > highestWidth) {
            highestWidth = width
          }
        })

      setSubQuestionsHeight([...subQuestionsHeight])
      setHighestWidth(highestWidth)
    })

    if (subQuestionsContainerRef.current) {
      subQuestionsContainerRef.current
        .querySelectorAll('.array-subquestion-content-editor')
        .forEach((item) => {
          observer.observe(item)
        })
      observer.observe(subQuestionsContainerRef.current)
    }

    return () => {
      observer.disconnect()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <div ref={subQuestionsContainerRef}>
      <DragAndDrop
        onDragEnd={(dropResult) => onDragEndCallback(dropResult)}
        droppableId={'droppable-subquestions'}
      >
        {subquestions?.map((subQuestion, index) => (
          <Draggable
            key={`${qid}-${index}-subquestion`}
            draggableId={`${qid}-${index}-subquestion`}
            index={index}
          >
            {(provided, snapshot) => (
              <div
                {...provided.draggableProps}
                ref={provided.innerRef}
                style={getAnswerStyle(provided.draggableProps.style)}
                className={classNames({
                  'focus-element': snapshot.isDragging,
                })}
              >
                <ArrayQuestionSubQuestion
                  arrayAnswersWidth={arrayAnswersWidth}
                  dragIconSize={dragIconSize}
                  handleRemovingSubQuestion={handleRemovingSubQuestion}
                  handleUpdateL10ns={handleUpdateL10ns}
                  isFocused={isFocused}
                  highestWidth={highestWidth}
                  index={index}
                  language={language}
                  answers={answers}
                  provided={provided}
                  subQuestion={subQuestion}
                  subQuestionsHeight={subQuestionsHeight}
                  questionThemeName={questionThemeName}
                  showContentEditor={showContentEditor}
                  scaleId={scaleId}
                  formFieldText={form_field_text}
                />
              </div>
            )}
          </Draggable>
        ))}
      </DragAndDrop>
    </div>
  )
}
