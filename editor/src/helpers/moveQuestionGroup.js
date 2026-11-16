export const moveQuestionGroup = (questionGroupsList, groupIndex, newIndex) => {
  const updatedList = [...questionGroupsList]
  const [removed] = updatedList.splice(groupIndex, 1)
  updatedList.splice(newIndex, 0, removed)

  return updatedList.map((questionGroup, index) => {
    questionGroup.sortOrder = index + 1
    return questionGroup
  })
}
