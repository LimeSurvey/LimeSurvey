export const MoveQuestionGroup = (questionGroups, currentIndex, newIndex) => {
  const reorderedQuestionGroups = insertQuestionGroup(
    questionGroups,
    currentIndex,
    newIndex
  )

  const movedQuestionGroup = reorderedQuestionGroups[newIndex]

  return { reorderedQuestionGroups, movedQuestionGroup, newIndex }
}

const insertQuestionGroup = (questionGroupsList, startIndex, endIndex) => {
  const updatedList = [...questionGroupsList]
  const [removed] = updatedList.splice(startIndex, 1)
  updatedList.splice(endIndex, 0, removed)

  return updatedList.map((questionGroup, index) => {
    questionGroup.groupOrder = index + 1
    return questionGroup
  })
}
