import {
  getNotSupportedQuestionTypeInfo,
  getQuestionTypeInfo,
} from 'components/QuestionTypes'

const arrayQuestionThemes = [
  getQuestionTypeInfo().ARRAY.theme,
  getQuestionTypeInfo().ARRAY_COLUMN.theme,
  getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
  getQuestionTypeInfo().ARRAY_NUMBERS.theme,
  getQuestionTypeInfo().ARRAY_TEXT.theme,
  getNotSupportedQuestionTypeInfo().ARRAY_YES_NO_UNCERTAIN.theme,
  getNotSupportedQuestionTypeInfo().ARRAY_INCREASE_SAME_DECREASE.theme,
]

export const isArrayQuestion = (questionThemeName) => {
  return arrayQuestionThemes.includes(questionThemeName)
}
