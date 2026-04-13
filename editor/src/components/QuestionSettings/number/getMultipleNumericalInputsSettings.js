import { getQuestionAttributesTitles } from 'helpers'

import {
  getStatisticsAttributes,
  getOtherAttributes,
  getLogicAttributes,
  getDisplayAttributes,
  getGeneralAttributes,
  getSliderAttributes,
  getInputAttributes,
} from '../attributes'

const simpleSettings = () => {
  const generalAttributes = getGeneralAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    generalAttributes.LOGIC,
    generalAttributes.ENCRYPTED,
    getDisplayAttributes().IMAGE_SETTINGS,
  ]
}

const generalSettings = () => {
  const generalAttributes = getGeneralAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    getSliderAttributes().USE_SLIDER_LAYOUT,
    generalAttributes.MANDATORY,
    generalAttributes.ENCRYPTED,
  ]
}

const logicSettings = () => {
  const logicAttributes = getLogicAttributes()
  return [
    logicAttributes.MINIMUM_ANSWERS,
    logicAttributes.MAXIMUM_ANSWERS,
    logicAttributes.ARRAY_FILTER,
    logicAttributes.ARRAY_FILTER_EXCLUSION,
    logicAttributes.ARRAY_FILTER_STYLE,
    logicAttributes.EXCLUSIVE_OPTIONS,
    logicAttributes.RANDOMIZATION_GROUP_NAME,
    logicAttributes.QUESTION_VALIDATION_EQUATION,
  ]
}

const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.LABEL_WRAPPER_WIDTH,
    displayAttributes.RANDOM_ORDER,
    displayAttributes.KEEP_CODE_ORDER,
    displayAttributes.TEXT_INPUT_WIDTH,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.CSS_CLASSES,
    displayAttributes.CONDITION_HELP_FOR_PRINTABLE_SURVEY,
  ]
}

const inputSettings = () => {
  const inputAttributes = getInputAttributes()
  return [
    inputAttributes.VALUE_RANGE_ALLOWS_MISSING,
    inputAttributes.INTEGER_ONLY,
    inputAttributes.MINIMUM_VALUE,
    inputAttributes.MAXIMUM_VALUE,
    inputAttributes.MINIMUM_SUM_VALUE,
    inputAttributes.MAXIMUM_SUM_VALUE,
  ]
}

const otherSettings = () => {
  return [getOtherAttributes().INSERT_PAGE_BREAK_IN_PRINTABLE_VIEW]
}

const statisticsSettings = () => {
  const statisticsAttributes = getStatisticsAttributes()
  return [
    statisticsAttributes.SHOW_IN_PUBLIC_STATISTICS,
    statisticsAttributes.SHOW_IN_STATISTICS,
  ]
}

const sliderSettings = () => {
  const sliderAttributes = getSliderAttributes()
  return [
    sliderAttributes.ORIENTATION,
    sliderAttributes.HANDLE_SHAPE,
    sliderAttributes.CUSTOM_HANDLE_UNICODE_CODE,
    sliderAttributes.SLIDER_STARTS_AT_THE_MIDDLE_POSITION,
    sliderAttributes.SLIDER_MINIMUM_VALUE,
    sliderAttributes.SLIDER_MAXIMUM_VALUE,
    sliderAttributes.SLIDER_ACCURACY,
    sliderAttributes.REVERSE_THE_SLIDER_DIRECTION,
    sliderAttributes.ALLOW_SLIDER_RESET,
    sliderAttributes.SLIDER_INITIAL_VALUE,
    sliderAttributes.SLIDER_INITIAL_VALUE_SET_AT_STARTS,
    sliderAttributes.DISPLAY_SLIDER_MIN_AND_MAX_VALUE,
    sliderAttributes.SLIDER_LEFT_RIGHT_TEXT_SEPARATOR,
  ]
}

const themeOptionsSettings = () => {
  return []
}

const fileMetaDataSettings = () => {
  return []
}

const locationSettings = () => {
  return []
}

export const getMultipleNumericalInputsSettings = () => {
  return [
    {
      title: getQuestionAttributesTitles().SIMPLE,
      attributes: simpleSettings(),
    },
    {
      title: getQuestionAttributesTitles().GENERAL,
      attributes: generalSettings(),
    },
    { title: getQuestionAttributesTitles().LOGIC, attributes: logicSettings() },
    {
      title: getQuestionAttributesTitles().DISPLAY,
      attributes: displaySettings(),
    },
    { title: getQuestionAttributesTitles().INPUT, attributes: inputSettings() },
    { title: getQuestionAttributesTitles().OTHER, attributes: otherSettings() },
    {
      title: getQuestionAttributesTitles().STATISTICS,
      attributes: statisticsSettings(),
    },
    {
      title: getQuestionAttributesTitles().THEME_OPTIONS,
      attributes: themeOptionsSettings(),
    },
    {
      title: getQuestionAttributesTitles().FILE_META_DATA,
      attributes: fileMetaDataSettings(),
    },
    {
      title: getQuestionAttributesTitles().LOCATION,
      attributes: locationSettings(),
    },
    {
      title: getQuestionAttributesTitles().SLIDER,
      attributes: sliderSettings(),
    },
    {
      title: ' ',
      attributes: [getGeneralAttributes().SAVE_AS_DEFAULT],
    },
  ]
}
