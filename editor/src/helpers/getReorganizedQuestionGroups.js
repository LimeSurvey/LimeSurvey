import { cloneDeep } from 'lodash'

export const getReorganizedQuestionGroups = (reorderedQuestionGroups) => {
  const updatedQuestionGroups = cloneDeep(reorderedQuestionGroups)

  return updatedQuestionGroups
    .map((group, index) => {
      const questions = group.questions
        .map((question, index) => {
          return {
            [question.qid]: {
              sortOrder: index + 1,
            },
          }
        })
        .reduce((a, b) => Object.assign(a, b), {}) // converting from array to object and keys, where the keys are id

      return {
        [group.gid]: {
          sortOrder: index + 1,
          questions,
        },
      }
    })
    .reduce((a, b) => Object.assign(a, b), {}) // converting from array to object and keys, where the keys are id
}
