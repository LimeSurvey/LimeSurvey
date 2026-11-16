import { getQuestionTypeInfo } from './getQuestionTypeInfo'
import { getNotSupportedQuestionTypeInfo } from './getNotSupportedQuestionTypeInfo'

export const singleChoiceThemes = [
  getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
  getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme,
  getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme,
  getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme,
  getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
  getNotSupportedQuestionTypeInfo().LANGUAGE_SWITCH.theme,
  getNotSupportedQuestionTypeInfo().LIST_DROPDOWN_DEFAULT.theme,
]
