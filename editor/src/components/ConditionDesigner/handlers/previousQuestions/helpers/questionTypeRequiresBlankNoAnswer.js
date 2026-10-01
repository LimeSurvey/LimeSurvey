import { getQuestionTypeInfo } from 'components/QuestionTypes'

export const questionTypeRequiresBlankNoAnswer = (type) => {
  const questionTypeInfo = getQuestionTypeInfo()
  const blankTypes = [
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
  ]
  return blankTypes.includes(type)
}
