import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { getNotSupportedQuestionTypeInfo } from '../getNotSupportedQuestionTypeInfo'

const questionTypeInfo = getQuestionTypeInfo()
const notSupportedQuestionTypeInfo = getNotSupportedQuestionTypeInfo()

const answersThemes = [
  questionTypeInfo.SINGLE_CHOICE_BUTTONS.theme,
  questionTypeInfo.SINGLE_CHOICE_DROPDOWN.theme,
  questionTypeInfo.SINGLE_CHOICE_FIVE_POINT_CHOICE.theme,
  questionTypeInfo.SINGLE_CHOICE_IMAGE_SELECT.theme,
  questionTypeInfo.SINGLE_CHOICE_LIST_RADIO.theme,
  questionTypeInfo.SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme,
  notSupportedQuestionTypeInfo.LIST_DROPDOWN_DEFAULT.theme,
]

export const isQuestionWithAnswers = (questionThemeName) => {
  return answersThemes.includes(questionThemeName)
}
