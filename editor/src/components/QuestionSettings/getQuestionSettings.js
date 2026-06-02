import { getQuestionTypeInfo } from 'components/QuestionTypes'

import {
  getShortTextSettings,
  getLongTextSettings,
  getMultipleShortTextSettings,
  getNumericSettings,
} from './textQuestion'

import {
  getBrowserDetectionSettings,
  getDateTimeSettings,
  getFileUploadSettings,
} from './datesAndData'
import {
  getEquationSettings,
  getMultipleNumericalInputsSettings,
  getTextDisplaySettings,
} from './number'
import {
  getListImageSelectSettings,
  getListRadioSettings,
  getListWithCommentRadioSettings,
  getFivePointChoiceSettings,
  getDropdownSettings,
  getSingleChoiceButtonsSettings,
} from './singleChoice'
import {
  getMultipleChoiceButtonsSettings,
  getMultipleChoiceImageSettings,
  getMultipleChoiceSettings,
  getMultipleChoiceWithCommentsSettings,
} from './multipleChoice'
import {
  getArrayByColumnSettings,
  getArrayDualScaleSettings,
  getArrayNumbersSettings,
  getArrayPointChoiceSettings,
  getArrayTextsSettings,
} from './array'
import {
  getGenderSettings,
  getRankingAdvancedSettings,
  getRankingSettings,
  getRatingSettings,
  getYesNoSettings,
} from './rankingAndRating'

export const getQuestionSettings = () => {
  return {
    [getQuestionTypeInfo().ARRAY.theme]: getArrayPointChoiceSettings(),
    [getQuestionTypeInfo().ARRAY_COLUMN.theme]: getArrayByColumnSettings(),
    [getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme]: getArrayDualScaleSettings(),
    [getQuestionTypeInfo().ARRAY_NUMBERS.theme]: getArrayNumbersSettings(),
    [getQuestionTypeInfo().ARRAY_TEXT.theme]: getArrayTextsSettings(),
    [getQuestionTypeInfo().BROWSER_DETECTION.theme]:
      getBrowserDetectionSettings(),
    [getQuestionTypeInfo().DATE_TIME.theme]: getDateTimeSettings(),
    [getQuestionTypeInfo().END_SCREEN.theme]: getShortTextSettings(),
    [getQuestionTypeInfo().EQUATION.theme]: getEquationSettings(),
    [getQuestionTypeInfo().FILE_UPLOAD.theme]: getFileUploadSettings(),
    [getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE.theme]:
      getFivePointChoiceSettings(),
    [getQuestionTypeInfo().GENDER.theme]: getGenderSettings(),
    [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme]:
      getListRadioSettings(),
    [getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme]:
      getListImageSelectSettings(),
    [getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme]:
      getListWithCommentRadioSettings(),
    [getQuestionTypeInfo().NUMERIC.theme]: getNumericSettings(),
    [getQuestionTypeInfo().SHORT_TEXT.theme]: getShortTextSettings(),
    [getQuestionTypeInfo().LONG_TEXT.theme]: getLongTextSettings(),
    [getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme]:
      getMultipleShortTextSettings(),
    [getQuestionTypeInfo().MULTIPLE_CHOICE.theme]: getMultipleChoiceSettings(),
    [getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme]:
      getMultipleChoiceButtonsSettings(),
    [getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme]:
      getMultipleChoiceImageSettings(),
    [getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme]:
      getMultipleChoiceWithCommentsSettings(),
    [getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme]:
      getMultipleNumericalInputsSettings(),
    [getQuestionTypeInfo().QUESTION_GROUP.theme]: getShortTextSettings(),
    [getQuestionTypeInfo().RANKING.theme]: getRankingSettings(),
    [getQuestionTypeInfo().RANKING_ADVANCED.theme]:
      getRankingAdvancedSettings(),
    [getQuestionTypeInfo().RATING.theme]: getRatingSettings(),
    [getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme]:
      getSingleChoiceButtonsSettings(),
    [getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme]: getDropdownSettings(),
    [getQuestionTypeInfo().TEXT_DISPLAY.theme]: getTextDisplaySettings(),
    [getQuestionTypeInfo().WELCOME_SCREEN.theme]: getShortTextSettings(),
    [getQuestionTypeInfo().YES_NO.theme]: getYesNoSettings(),
  }
}
