import { getQuestionById } from './getQuestionById'

/**
 * Find the focused question or group again in the current survey.
 *
 * The focus state stores the indexes of the focused entity. They become
 * wrong when the survey changes (a group is deleted or reordered, the
 * survey is refetched). This looks the entity up by its qid / gid.
 *
 * @param {object} focusState The focus state: { focused, groupIndex, questionIndex }
 * @param {object} survey The current survey
 * @returns {object|null|undefined} The focus state with updated indexes,
 *   null if the focused entity no longer exists, or undefined if nothing
 *   has to change
 */
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
