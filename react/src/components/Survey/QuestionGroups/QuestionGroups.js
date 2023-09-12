import { useFocused } from 'hooks'
import { ConfirmAlert, DuplicateQuestionGroup } from 'helpers'

import QuestionGroup from './QuestionGroup'

export const QuestionGroups = ({
  language,
  defaultLanguage,
  questionGroups = [],
  update,
}) => {
  const { focused = {}, setFocused, unFocus } = useFocused()

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
      questionGroup.groupOrder = index + 1
      return questionGroup
    })

    update(updatedQuestionGroups)
    setFocused(newQuestionGroup, newQuestionGroupIndex)
  }

  const handleGroupDeletion = (index) => {
    ConfirmAlert({ icon: 'warning' }).then(({ isConfirmed }) => {
      if (!isConfirmed) {
        return
      }

      const updatedQuestionGroups = [...questionGroups]
      updatedQuestionGroups.splice(index, 1)
      update(updatedQuestionGroups)

      if (!focused.qid && focused.gid === questionGroups[index].gid) {
        unFocus()
      }
    })
  }

  const handleQuestionGroupDuplication = (questionGroup, index) => {
    ++index
    const { duplicatedQuestionGroup, updatedQuestionGroups } =
      DuplicateQuestionGroup(questionGroup, questionGroups, index)

    update(updatedQuestionGroups)
    setFocused(duplicatedQuestionGroup, index)
  }

  // Keeps a running count on how many questions we have
  let previousQuestionsTotal = 0

  return (
    <div>
      {questionGroups.map((questionGroup, index) => {
        previousQuestionsTotal += questionGroup.questions.length
        return (
          <div key={`question-group-${questionGroup.gid}`}>
            <QuestionGroup
              language={language}
              defaultLanguage={defaultLanguage}
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
            />
          </div>
        )
      })}
    </div>
  )
}
