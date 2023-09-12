import { Select } from 'components'
import { L10ns, MoveQuestionToGroup } from 'helpers'
import { useEffect, useState } from 'react'

export const QuestionGroup = ({
  questionGroups = [],
  question = {},
  language,
  groupIndex,
  questionIndex,
  update,
}) => {
  const [selectQuestionGroup, setSelectedQuestionGroup] = useState({})
  const [questionGroupOptions, setQuestionGroupOptions] = useState([])

  useEffect(() => {
    const questionGroupOptions = questionGroups.map((questionGroup) => {
      const title =
        L10ns({
          prop: 'groupName',
          language,
          l10ns: questionGroup.l10ns,
        }) || "What's your question group is about?"

      if (questionGroup.gid === question.gid) {
        setSelectedQuestionGroup({
          value: questionGroup.gid,
          label: title,
        })
      }

      return {
        value: questionGroup.gid,
        label: title,
      }
    })

    setQuestionGroupOptions(questionGroupOptions)

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [language, question.gid, questionGroups.length])

  const handleQuestionGroupChange = (questionGroup) => {
    const questionGroupGid = +questionGroup.target.value

    if (questionGroupGid === question.gid) {
      return
    }

    const newGroupIndex = questionGroups.findIndex(
      (group) => group.gid === questionGroupGid
    )

    if (newGroupIndex === -1) {
      return
    }

    const currentGroup = questionGroups[groupIndex]
    const newGroup = questionGroups[newGroupIndex]

    const { destinationGroup, sourceGroup } = MoveQuestionToGroup(
      questionIndex,
      currentGroup,
      newGroup
    )

    const updatedQuestionGroups = [...questionGroups]
    updatedQuestionGroups[groupIndex] = sourceGroup
    updatedQuestionGroups[newGroupIndex] = destinationGroup

    const title =
      L10ns({
        prop: 'groupName',
        language,
        l10ns: newGroup.l10ns,
      }) || "What's your question group is about?"

    setSelectedQuestionGroup({
      value: newGroup.gid,
      label: title,
    })

    update([...updatedQuestionGroups])
  }

  return (
    <Select
      labelText="Question group"
      options={questionGroupOptions}
      onChange={handleQuestionGroupChange}
      selectedOption={selectQuestionGroup}
    />
  )
}
