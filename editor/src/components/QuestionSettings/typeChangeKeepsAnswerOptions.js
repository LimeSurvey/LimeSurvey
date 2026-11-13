// True if both types share the same answer option layout
export const typeChangeKeepsAnswerOptions = (
  answerOptionLayouts = {},
  oldType,
  newType
) => {
  const oldLayout = answerOptionLayouts[oldType]

  return !!oldLayout && oldLayout === answerOptionLayouts[newType]
}
