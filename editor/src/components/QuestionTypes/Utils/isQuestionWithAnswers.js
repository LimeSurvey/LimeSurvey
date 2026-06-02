import { getQuestionTypeInfo } from '../getQuestionTypeInfo'

const questionTypeInfo = getQuestionTypeInfo()

const answersThemes = [
  questionTypeInfo.SINGLE_CHOICE_BUTTONS.theme,
  questionTypeInfo.SINGLE_CHOICE_DROPDOWN.theme,
  questionTypeInfo.SINGLE_CHOICE_FIVE_POINT_CHOICE.theme,
  questionTypeInfo.SINGLE_CHOICE_IMAGE_SELECT.theme,
  questionTypeInfo.SINGLE_CHOICE_LIST_RADIO.theme,
  questionTypeInfo.SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme,
]

export const isQuestionWithAnswers = (questionThemeName) => {
  return answersThemes.includes(questionThemeName)
}
