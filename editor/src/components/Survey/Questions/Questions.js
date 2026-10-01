import React from 'react'

import {
  createBufferOperation,
  arrayDeleteItem,
  DuplicateQuestion,
  MoveQuestion,
  getReorganizedQuestions,
  InsertElementAndIncrementProperty,
  updateQuestionsSortOrder,
} from 'helpers'
import { useBuffer, useFocused } from 'hooks'

import { Question } from './Question'

export const Questions = ({
  language,
  questions = [],
  update,
  firstQuestionNumber,
  questionGroupIsOpen,
  groupIndex,
  questionGroup,
  surveySettings,
}) => {
  const { setFocused, unFocus, questionIndex } = useFocused()
  const { addToBuffer } = useBuffer()
  let questionNumber = firstQuestionNumber

  const handleUpdate = (index, change, isInserting = false) => {
    const lastSliceIndex = isInserting ? index : index + 1

    update([
      ...questions.slice(0, index),
      change,
      ...questions.slice(lastSliceIndex),
    ])
  }

  const handleRemovingQuestion = (index) => {
    unFocus()
    const [updatedQuestions] = arrayDeleteItem(questions, index)
    const updatedQuestionsSortOrder = updateQuestionsSortOrder(updatedQuestions)
    update(updatedQuestionsSortOrder)
  }

  const handleSwapQuestionPosition = (direction) => {
    const newIndex = questionIndex + direction

    if (newIndex < 0 || newIndex >= questions.length) {
      return
    }

    const { reorderedQuestions, movedQuestion } = MoveQuestion(
      questions,
      questionIndex,
      newIndex
    )

    const sortOrder = groupIndex + 1
    const props = {
      [movedQuestion.gid]: {
        sortOrder: sortOrder,
        questions: getReorganizedQuestions(reorderedQuestions),
      },
    }
    const operation = createBufferOperation()
      .questionGroupReorder()
      .update(props)
    addToBuffer(operation)
    questionGroup.questions = reorderedQuestions
    update(reorderedQuestions)
    setFocused(movedQuestion, groupIndex, newIndex)
  }

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

    handleUpdate(cloneIndex, duplicatedQuestion, true)
    setFocused(updatedQuestions[cloneIndex], groupIndex, cloneIndex)
  }

  return (
    <>
      {questions.map((question, index) => (
        <React.Fragment
          key={`question-${question.title}-${index}-${question.gid}`}
        >
          <Question
            language={language}
            question={question}
            update={(question) => handleUpdate(index, question)}
            handleRemove={() => handleRemovingQuestion(index)}
            handleDuplicate={() => {
              duplicateQuestion(question, index)
            }}
            questionNumber={++questionNumber}
            groupIndex={groupIndex}
            questionIndex={index}
            lastQuestionIndex={questions.length - 1}
            questionGroupIsOpen={questionGroupIsOpen}
            handleSwapQuestionPosition={handleSwapQuestionPosition}
            surveySettings={surveySettings}
          />
          {index !== questions.length - 1 ? <hr className="my-0" /> : ''}
        </React.Fragment>
      ))}
    </>
  )
}
