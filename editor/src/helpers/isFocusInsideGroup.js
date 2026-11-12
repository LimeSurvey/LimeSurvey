/**
 * Check whether the focused entity is the given question group itself
 * or one of the questions inside it.
 *
 * @param {object} focused The currently focused entity
 * @param {object} questionGroup The question group to check against
 * @returns {boolean} True if the focus is on the group or one of its questions
 */
export const isFocusInsideGroup = (focused, questionGroup) => {
  if (!focused || !questionGroup) return false

  if (focused.qid !== undefined) {
    return (questionGroup.questions ?? []).some(
      (question) => question.qid?.toString() === focused.qid?.toString()
    )
  }

  return (
    focused.gid !== undefined &&
    focused.gid?.toString() === questionGroup.gid?.toString()
  )
}
