import { getQuestionTypeInfo } from 'components/QuestionTypes'

const arrayQuestionThemes = [
  getQuestionTypeInfo().ARRAY.theme,
  getQuestionTypeInfo().ARRAY_COLUMN.theme,
  getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
  getQuestionTypeInfo().ARRAY_NUMBERS.theme,
  getQuestionTypeInfo().ARRAY_TEXT.theme,
]

export const isArrayQuestion = (questionThemeName) => {
  return arrayQuestionThemes.includes(questionThemeName)
}
