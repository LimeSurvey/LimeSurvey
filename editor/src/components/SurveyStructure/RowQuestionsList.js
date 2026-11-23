import { useState } from 'react'
import { useParams } from 'react-router-dom'
import { Draggable, Droppable } from '@hello-pangea/dnd'
import classNames from 'classnames'

import {
  useAppState,
  useBuffer,
  useEventCallback,
  useFocused,
  useToggleQuestionCodes,
} from 'hooks'
import {
  arrayDeleteItem,
  DuplicateQuestion,
  createBufferOperation,
  Entities,
  getNumericAnswerCodes,
  hasNumericAnswerCodes,
  STATES,
} from 'helpers'
import { createChildrenUpdateOperation } from 'hooks/useQuestionChildren'
import { ConfirmModal } from 'components/Modals'

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
  const [deleteState, setDeleteState] = useState({ show: false, index: null })
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const { surveyId } = useParams()
  // Read once per list instead of per row to avoid a subscription per question
  const { areCodesVisible, toggleQuestionCodes } =
    useToggleQuestionCodes(surveyId)

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

  const assignNumericAnswerCodes = (question, index) => {
    if (hasNumericAnswerCodes(question.answers)) {
      return false
    }
    const answers = getNumericAnswerCodes(question.answers)
    const updatedQuestion = { ...question, answers }
    const updatedQuestions = [...questions]
    updatedQuestions[index] = updatedQuestion
    addToBuffer(
      createChildrenUpdateOperation(question.qid, Entities.answer, answers)
    )
    handleUpdate(updatedQuestions)
    if (focused?.qid === question.qid) {
      setFocused(updatedQuestion, groupIndex, index)
    }
    return true
  }

  const deleteQuestion = (index) => {
    setDeleteState({ show: true, index })
  }

  const handleConfirmDelete = () => {
    const { index } = deleteState
    const questionToDelete = questions[index]
    const [updatedQuestions] = arrayDeleteItem(questions, index)
    const operation = createBufferOperation(questionToDelete.qid)
      .question()
      .delete()

    addToBuffer(operation)
    handleUpdate(updatedQuestions)
    unFocus()
    setDeleteState({ show: false, index: null })
  }

  // Same functions on every render, so the memoized rows only render again
  // when their own props change (the drag library also renders the rows on
  // every pointer move)
  const handleSelect = useEventCallback((question, index) =>
    setFocused({ ...question }, groupIndex, index)
  )
  const handleDuplicate = useEventCallback(duplicateQuestion)
  const handleDelete = useEventCallback(deleteQuestion)
  const handleAssignNumericAnswerCodes = useEventCallback(
    assignNumericAnswerCodes
  )
  const handleToggleQuestionCodes = useEventCallback(toggleQuestionCodes)

  return (
    <>
      <ConfirmModal
        show={deleteState.show}
        onHide={() => setDeleteState({ show: false, index: null })}
        onConfirm={handleConfirmDelete}
        title={t('Delete question')}
        description={t(
          'Are you sure you want to delete this question? This action cannot be reverted.'
        )}
        confirmButtonText={t('Delete')}
      />
      <Droppable
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
            {questions.map((question, index) => (
              // Keyed by the question, not its position: A reorder moves the
              // existing rows instead of rebuilding them.
              <Draggable
                key={question.qid}
                draggableId={`question-${question.qid}`}
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
                      surveyId={surveyId}
                      dragHandleProps={provided.dragHandleProps}
                      questionIndex={index}
                      isDragging={snapshot.isDragging}
                      isFocused={focused?.qid === question.qid}
                      isSurveyActive={isSurveyActive}
                      areCodesVisible={areCodesVisible}
                      onSelect={handleSelect}
                      duplicateQuestion={handleDuplicate}
                      deleteQuestion={handleDelete}
                      assignNumericAnswerCodes={handleAssignNumericAnswerCodes}
                      toggleQuestionCodes={handleToggleQuestionCodes}
                    />
                  </div>
                )}
              </Draggable>
            ))}
            {provided.placeholder}
            {questions.length === 0 && (
              <div>{t('Question group is empty.')}</div>
            )}
          </div>
        )}
      </Droppable>
    </>
  )
}
