import { getQuestionTypeInfo } from 'components/QuestionTypes'

// Built once: getQuestionTypeInfo() builds (and translates) every type, and
// this runs for each source question of the Condition Designer. The type codes
// never change.
let blankTypes
const getBlankTypes = () => {
  if (!blankTypes) {
    const questionTypeInfo = getQuestionTypeInfo()
    blankTypes = new Set([
      'A',
      'B',
      'C',
      'E',
      questionTypeInfo.ARRAY.type,
      questionTypeInfo.ARRAY_COLUMN.type,
      questionTypeInfo.ARRAY_NUMBERS.type,
      questionTypeInfo.ARRAY_TEXT.type,
      questionTypeInfo.ARRAY_DUAL_SCALE.type,
      questionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.type,
      questionTypeInfo.MULTIPLE_SHORT_TEXTS.type,
    ])
  }
  return blankTypes
}

export const questionTypeRequiresBlankNoAnswer = (type) =>
  getBlankTypes().has(type)
