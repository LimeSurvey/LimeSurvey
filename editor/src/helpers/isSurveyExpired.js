export const isSurveyExpired = (expires) => {
  if (!expires) {
    return false
  }

  const now = new Date()
  return now > new Date(expires)
}
