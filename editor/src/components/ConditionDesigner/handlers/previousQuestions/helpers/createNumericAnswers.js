import { createAnswer } from '../helpers'

export const createNumericAnswers = (cAnswers, count, start = 1, fieldname) => {
  for (let i = start; i <= count; i++) {
    createAnswer(cAnswers, i, i, fieldname)
  }
}
