import { cloneDeep } from 'lodash'

export const getReorganizedQuestions = (reorderedQuestions) => {
  const updatedQuestions = cloneDeep(reorderedQuestions)

  return updatedQuestions
    .map((question) => {
      return {
        [question.qid]: {
          sortOrder: question.sortOrder,
        },
      }
    })
    .reduce((a, b) => Object.assign(a, b), {}) // converting from array to object and keys, where the keys are id
}
