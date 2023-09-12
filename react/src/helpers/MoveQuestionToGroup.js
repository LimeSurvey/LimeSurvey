// Moves the question to a new group, will be the last index.
export const MoveQuestionToGroup = (
  questionIndex,
  sourceGroup,
  destinationGroup
) => {
  const sourceGroupCopy = { ...sourceGroup }
  const destinationGroupCopy = { ...destinationGroup }

  // Remove the item from the source list
  const [removedItem] = sourceGroupCopy.questions.splice(questionIndex, 1)
  removedItem.gid = destinationGroup.gid

  // Update the order property of the remaining items in the source list
  for (let i = questionIndex; i < sourceGroupCopy.length; i++) {
    sourceGroupCopy[i].order -= 1
  }

  sourceGroupCopy.questions = sourceGroupCopy.questions.map(
    (question, index) => {
      return { ...question, questionOrder: index + 1 }
    }
  )

  // Add the item to the destination list
  destinationGroupCopy.questions.push(removedItem)

  destinationGroupCopy.questions = destinationGroupCopy.questions.map(
    (question, index) => {
      return { ...question, questionOrder: index + 1 }
    }
  )

  // Return the updated lists
  return {
    sourceGroup: sourceGroupCopy,
    destinationGroup: destinationGroupCopy,
  }
}
