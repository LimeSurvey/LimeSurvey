export const getGroupById = (id, survey) => {
  for (let i = 0; i < survey.questionGroups.length; i++) {
    const group = survey.questionGroups[i]
    if (group.gid === id) {
      return { group, index: i }
    }
  }

  return {}
}
