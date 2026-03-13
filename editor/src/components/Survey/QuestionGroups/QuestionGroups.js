import { useMemo } from 'react'

import { useBuffer, useFocused } from 'hooks'
import {
  confirmAlert,
  createBufferOperation,
  DuplicateQuestion,
  DuplicateQuestionGroup,
  getReorganizedQuestionGroups,
  InsertElementAndIncrementProperty,
  RandomNumber,
} from 'helpers'

import QuestionGroup from './QuestionGroup'

export const QuestionGroups = ({
  language,
  questionGroups = [],
  update,
  surveySettings,
}) => {
  const { focused = {}, setFocused, unFocus } = useFocused()
  const { addToBuffer } = useBuffer()

  const keys = useMemo(() => {
    return [...Array(questionGroups.length)].map(() => `Q${RandomNumber()}`)
  }, [questionGroups.length])

  const handleUpdate = (index, questionGroup) => {
    update([
      ...questionGroups.slice(0, index),
      questionGroup,
      ...questionGroups.slice(index + 1),
    ])
  }

  const addQuestionGroup = (index, newQuestionGroup) => {
    const newQuestionGroupIndex = index + 1

    const updatedQuestionGroups = [
      ...questionGroups.slice(0, newQuestionGroupIndex),
      newQuestionGroup,
      ...questionGroups.slice(newQuestionGroupIndex),
    ].map((questionGroup, index) => {
      questionGroup.sortOrder = index + 1
      return questionGroup
    })

    update(updatedQuestionGroups)
    setFocused(newQuestionGroup, newQuestionGroupIndex)
  }

  const handleGroupDeletion = (index) => {
    confirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (!isConfirmed) {
        return
      }

      const updatedQuestionGroups = [...questionGroups]
      updatedQuestionGroups.splice(index, 1)
      update(updatedQuestionGroups)

      const operation = createBufferOperation(questionGroups[index].gid)
        .questionGroup()
        .delete()

      addToBuffer(operation)

      if (!focused.qid && focused.gid === questionGroups[index].gid) {
        unFocus()
      }
    })
  }

  const handleQuestionGroupDuplication = (questionGroup, index) => {
    const duplicatedQuestionGroup = DuplicateQuestionGroup(questionGroup)
    const updatedQuestionGroups = InsertElementAndIncrementProperty(
      questionGroups,
      duplicatedQuestionGroup,
      index + 1,
      'sortOrder'
    )

    const operation = createBufferOperation(duplicatedQuestionGroup.gid)
      .questionGroup()
      .create({
        questionGroup: {
          ...duplicatedQuestionGroup,
          tempId: duplicatedQuestionGroup.gid,
        },
        questionGroupL10n: duplicatedQuestionGroup.l10ns,
      })

    addToBuffer(operation)

    duplicatedQuestionGroup.questions.map((question) => {
      const duplicatedQuestion = DuplicateQuestion(question)

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
    })

    const reorderOperation = createBufferOperation()
      .questionGroupReorder()
      .update(getReorganizedQuestionGroups(updatedQuestionGroups))

    addToBuffer(reorderOperation)
    update(updatedQuestionGroups)
    setFocused(duplicatedQuestionGroup, index + 1)
  }

  // Keeps a running count on how many questions we have
  let previousQuestionsTotal = 0

  return (
    <div>
      {questionGroups.map((questionGroup, index) => {
        previousQuestionsTotal += questionGroup.questions.length
        return (
          <div key={`questionGroup-${keys[index]}`}>
            <QuestionGroup
              language={language}
              questionGroup={questionGroup}
              addQuestionGroup={(questionGroup) =>
                addQuestionGroup(index, questionGroup)
              }
              update={(questionGroup) => handleUpdate(index, questionGroup)}
              duplicateGroup={() =>
                handleQuestionGroupDuplication(questionGroup, index)
              }
              groupIndex={index}
              deleteGroup={() => handleGroupDeletion(index)}
              firstQuestionNumber={
                previousQuestionsTotal - questionGroup.questions.length
              }
              surveySettings={surveySettings}
            />
          </div>
        )
      })}
    </div>
  )
}
