import { LANGUAGE_CODES } from 'helpers'
import { RandomNumber } from './RandomNumber'

export const DuplicateQuestionGroup = (
  questionGroup,
  questionGroups,
  index
) => {
  const languages = Object.values(LANGUAGE_CODES)
  const newDuplicatedGroupId = questionGroup.gid + RandomNumber()
  const duplicatedQuestionGroup = {
    ...questionGroup,
    l10ns: {
      ...questionGroup.l10ns,
      ...languages.reduce((l10ns, language) => {
        if (!questionGroup.l10ns[language]) {
          return {
            ...l10ns,
          }
        }

        return {
          ...l10ns,
          [language]: {
            ...questionGroup.l10ns[language],
            groupName: `${questionGroup.l10ns[language].groupName}`,
            gid: newDuplicatedGroupId,
          },
        }
      }, {}),
    },
    gid: newDuplicatedGroupId,
  }

  duplicatedQuestionGroup.questions = duplicatedQuestionGroup.questions.map(
    (question) => ({
      ...question,
      qid: question.qid + RandomNumber(),
    })
  )

  let updatedQuestionGroups = [...questionGroups]
  updatedQuestionGroups.splice(index, 0, duplicatedQuestionGroup)

  updatedQuestionGroups = updatedQuestionGroups.map((questionGroup, index) => {
    questionGroup.groupOrder = index + 1
    return questionGroup
  })

  return {
    updatedQuestionGroups,
    duplicatedQuestionGroup,
  }
}
