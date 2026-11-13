// Check whether the focus is on the given group or one of its questions
export const isFocusInsideGroup = (focused, questionGroup) => {
  if (!focused || !questionGroup) return false

  if (focused.qid != null) {
    return (questionGroup.questions ?? []).some(
      (question) => question.qid?.toString() === focused.qid?.toString()
    )
  }

  return (
    focused.gid != null &&
    focused.gid?.toString() === questionGroup.gid?.toString()
  )
}
