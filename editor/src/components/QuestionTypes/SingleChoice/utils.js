import { getNotSupportedQuestionTypeInfo } from '../getNotSupportedQuestionTypeInfo'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'

export const dropdownThemeComponents = [
  getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme,
  getNotSupportedQuestionTypeInfo().LANGUAGE_SWITCH.theme,
  getNotSupportedQuestionTypeInfo().LIST_DROPDOWN_DEFAULT.theme,
]
