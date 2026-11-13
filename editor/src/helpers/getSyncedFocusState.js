import { getQuestionById } from './getQuestionById'

// Re-find the focused entity by qid/gid: Returns the updated state, null if gone, undefined if unchanged
export const getSyncedFocusState = (focusState, survey) => {
  const focused = focusState?.focused
  if (!focused || !Array.isArray(survey?.questionGroups)) {
    return undefined
  }

  if (focused.qid != null) {
    const { question, groupIndex, questionIndex } = getQuestionById(
      focused.qid,
      survey
    )
    if (!question) {
      return null
    }
    if (
      groupIndex === focusState.groupIndex &&
      questionIndex === focusState.questionIndex
    ) {
      return undefined
    }
    return { ...focusState, groupIndex, questionIndex }
  }

  if (focused.gid != null) {
    const groupIndex = survey.questionGroups.findIndex(
      (group) => group.gid?.toString() === focused.gid?.toString()
    )
    if (groupIndex === -1) {
      return null
    }
    if (groupIndex === focusState.groupIndex) {
      return undefined
    }
    return { ...focusState, groupIndex }
  }

  // Welcome / end screen: Not part of the question groups
  return undefined
}
