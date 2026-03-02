import { Draggable, Droppable } from 'react-beautiful-dnd'
import classNames from 'classnames'

import { useBuffer, useFocused } from 'hooks'
import {
  confirmAlert,
  arrayDeleteItem,
  DuplicateQuestion,
  createBufferOperation,
} from 'helpers'

import { RowQuestion } from './RowQuestion'
import { InsertElementAndIncrementProperty } from 'helpers/InsertElementAndIncrementProperty'

export const RowQuestionsList = ({
  questions = [],
  handleUpdate,
  language,
  groupIndex,
}) => {
  const { addToBuffer } = useBuffer()
  const { focused = {}, setFocused, unFocus } = useFocused()

  const getQuestionDragStyle = (draggableStyle) => ({
    userSelect: 'none',
    margin: '0',
    ...draggableStyle,
  })

  const duplicateQuestion = (question, questionIndex) => {
    const cloneIndex = questionIndex + 1
    const duplicatedQuestion = DuplicateQuestion(question)
    const updatedQuestions = InsertElementAndIncrementProperty(
      questions,
      duplicatedQuestion,
      cloneIndex,
      'sortOrder'
    )

    const operation = createBufferOperation(duplicatedQuestion.qid)
      .question()
      .create({
        question: { ...duplicatedQuestion, tempId: duplicatedQuestion.qid },
        questionL10n: { ...duplicatedQuestion.l10ns },
        attributes: { ...(duplicatedQuestion.attributes || {}) },
        answers: { ...(duplicatedQuestion.answers || []) },
        subquestions: { ...(duplicatedQuestion.subquestions || []) },
      })

    addToBuffer(operation)

    for (let i = cloneIndex + 1; i < updatedQuestions.length; i++) {
      const question = updatedQuestions[i]
      const operation = createBufferOperation(question.qid)
        .question()
        .update({ sortOrder: question.sortOrder })

      addToBuffer(operation)
    }

    handleUpdate(updatedQuestions)
    setTimeout(() => {
      setFocused(updatedQuestions[cloneIndex], groupIndex, cloneIndex)
    }, 0)
  }

  const deleteQuestion = (index) => {
    confirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (!isConfirmed) {
        return
      }

      const questionToDelete = questions[index]
      const [updatedQuestions] = arrayDeleteItem(questions, index)
      const operation = createBufferOperation(questionToDelete.qid)
        .question()
        .delete()

      addToBuffer(operation)
      handleUpdate(updatedQuestions)
      unFocus()
    })
  }
  return (
    <Droppable
      key={`g${groupIndex}-${questions?.length}`}
      droppableId={`g${groupIndex}`}
      type="question"
      direction="vertical"
    >
      {(provided) => (
        <div
          {...provided.droppableProps}
          ref={provided.innerRef}
          className="group"
        >
          {questions.map((question, index) => {
            return (
              <Draggable
                key={`g${groupIndex}_q${index}_${question.qid}`}
                draggableId={`g${groupIndex}_q${index}`}
                index={index}
              >
                {(provided, snapshot) => (
                  <div
                    ref={provided.innerRef}
                    {...provided.draggableProps}
                    style={getQuestionDragStyle(provided.draggableProps.style)}
                    data-sortorder={question.sortOrder}
                    className={classNames({
                      'focus-element': snapshot.isDragging,
                    })}
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
                      focused={focused}
                      snapshot={snapshot}
                    />
                  </div>
                )}
              </Draggable>
            )
          })}
          {provided.placeholder}
          {questions.length === 0 && <div>{t('Question group is empty.')}</div>}
        </div>
      )}
    </Droppable>
  )
}
