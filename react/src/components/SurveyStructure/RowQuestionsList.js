import { useState } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import classNames from 'classnames'

import { DragAndDrop } from 'components'
import { useFocused } from 'hooks'
import {
  DeleteItemFromArray,
  DuplicateQuestion,
  ConfirmAlert,
  MoveQuestion,
} from 'helpers'

import { RowQuestion } from './RowQuestion'

export const RowQuestionsList = ({
  questions,
  handleUpdate,
  language,
  groupIndex,
  showQuestionCode,
}) => {
  const [isReorderingQuestions, setIsReorderingQuestions] = useState(false)

  const { focused = {}, setFocused, unFocus } = useFocused()

  const getQuestionDragStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: '0',
    ...draggableStyle,
  })

  const handleOnDragEnd = (dropResult) => {
    setIsReorderingQuestions(false)

    // dropped outside the list
    if (!dropResult.destination) {
      return
    }

    const currentIndex = dropResult.source.index
    const newIndex = dropResult.destination.index

    const { movedQuestion, reorderedQuestions } = MoveQuestion(
      questions,
      currentIndex,
      newIndex
    )

    handleUpdate(reorderedQuestions)
    setFocused(movedQuestion, groupIndex, newIndex)
  }

  const duplicateQuestion = (question, index) => {
    ++index
    const { duplicatedQuestion, updatedQuestions } = DuplicateQuestion(
      question,
      questions,
      index
    )

    handleUpdate(updatedQuestions)
    setFocused(duplicatedQuestion, groupIndex, index)
  }

  const deleteQuestion = (index) => {
    ConfirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (!isConfirmed) {
        return
      }

      const updatedQuestions = DeleteItemFromArray(questions, index)
      handleUpdate(updatedQuestions)

      if (focused.qid === questions[index].qid) {
        unFocus()
      }
    })
  }

  return (
    <>
      {questions.length > 0 ? (
        <DragAndDrop
          onDragStart={() => setIsReorderingQuestions(true)}
          onDragEnd={handleOnDragEnd}
          droppableId={'droppable'}
          className={classNames('', {
            'focus-element': isReorderingQuestions,
          })}
        >
          {questions.map((question, index) => {
            return (
              <Draggable
                key={`question-structure-${question.qid}-${question.gid}`}
                draggableId={`question-structure-${question.qid}-${question.gid}`}
                index={index}
              >
                {(provided, snapshot) => (
                  <div
                    ref={provided.innerRef}
                    {...provided.draggableProps}
                    style={getQuestionDragStyle(provided.draggableProps.style)}
                    className={classNames('question-body-content', {
                      'focus-element': snapshot.isDragging,
                      'opacity-25': question.attributes?.hide_question?.value,
                      'focus-bg-purple': focused.qid === question.qid,
                    })}
                    data-questionorder={question.questionOrder}
                  >
                    <RowQuestion
                      question={question}
                      language={language}
                      provided={provided}
                      duplicateQuestion={() =>
                        duplicateQuestion(question, index)
                      }
                      deleteQuestion={() => deleteQuestion(index)}
                      groupIndex={groupIndex}
                      questionIndex={index}
                      showQuestionCode={showQuestionCode}
                    />
                  </div>
                )}
              </Draggable>
            )
          })}
        </DragAndDrop>
      ) : (
        <div>Question group is empty.</div>
      )}
    </>
  )
}
