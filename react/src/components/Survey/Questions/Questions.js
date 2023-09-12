import React from 'react'

import {
  DeleteItemFromArray,
  ConfirmAlert,
  DuplicateQuestion,
  MoveQuestion,
} from 'helpers'
import { useFocused } from 'hooks'

import { Question } from './Question'

export const Questions = ({
  language,
  questions = [],
  update,
  firstQuestionNumber,
  questionGroupIsOpen,
  groupIndex,
}) => {
  const { setFocused, unFocus, questionIndex } = useFocused()
  let questionNumber = firstQuestionNumber

  const handleUpdate = (index, change) => {
    update([
      ...questions.slice(0, index),
      change,
      ...questions.slice(index + 1),
    ])
  }

  const handleRemovingQuestion = (index) => {
    ConfirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (isConfirmed) {
        unFocus()
        const updatedQuestions = DeleteItemFromArray(questions, index)

        update(updatedQuestions)
      }
    })
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

    update(reorderedQuestions)
    setFocused(movedQuestion, groupIndex, newIndex)
  }

  const duplicateQuestion = (question, index) => {
    ++index
    const { duplicatedQuestion, updatedQuestions } = DuplicateQuestion(
      question,
      questions,
      index
    )

    update(updatedQuestions)
    setFocused(duplicatedQuestion, groupIndex, index)
  }

  return (
    <div>
      {questions.map((question, index) => (
        <div key={`question-${index}-${question.qid}`}>
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
          />
          {index !== questions.length - 1 ? <hr className="my-0" /> : ''}
        </div>
      ))}
    </div>
  )
}
