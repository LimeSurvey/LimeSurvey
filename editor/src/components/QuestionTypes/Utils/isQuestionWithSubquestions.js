import { getQuestionTypeInfo } from '../getQuestionTypeInfo'

const questionTypeInfo = getQuestionTypeInfo()

const subquestionsThemes = [
  questionTypeInfo.MULTIPLE_CHOICE.theme,
  questionTypeInfo.MULTIPLE_CHOICE_BUTTONS.theme,
  questionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.theme,
  questionTypeInfo.MULTIPLE_CHOICE_IMAGE_SELECT.theme,
  questionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.theme,
  questionTypeInfo.MULTIPLE_SHORT_TEXTS.theme,
]

export const isQuestionWithSubquestions = (questionThemeName) => {
  return subquestionsThemes.includes(questionThemeName)
}
