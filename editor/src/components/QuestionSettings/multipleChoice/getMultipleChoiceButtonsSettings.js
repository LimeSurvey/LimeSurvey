import { getQuestionAttributesTitles } from 'helpers'

import {
  getTimerAttributes,
  getStatisticsAttributes,
  getOtherAttributes,
  getLogicAttributes,
  getDisplayAttributes,
  getGeneralAttributes,
  getThemeAttributes,
} from '../attributes'

const simpleSettings = () => {
  const generalAttributes = getGeneralAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    getDisplayAttributes().IMAGE_SETTINGS,
    generalAttributes.LOGIC,
    getStatisticsAttributes().SHOW_IN_STATISTICS,
  ]
}

const generalSettings = () => {
  const generalAttributes = getGeneralAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    generalAttributes.ENCRYPTED,
    generalAttributes.SAVE_AS_DEFAULT,
    generalAttributes.OTHER,
  ]
}

const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.IMAGE_SETTINGS,
    displayAttributes.LABEL_FOR_OTHER_OPTIONS,
    displayAttributes.POSITION_FOR_OTHER_OPTION,
    displayAttributes.SUBQUESTION_TITLE,
    displayAttributes.SUBQUESTION_OPTIONS_ORDER,
    displayAttributes.KEEP_CODE_ORDER,
    displayAttributes.HIDE_TIP,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.CSS_CLASSES,
  ]
}

const logicSettings = () => {
  const logicAttributes = getLogicAttributes()
  return [
    logicAttributes.MINIMUM_ANSWERS,
    logicAttributes.MAXIMUM_ANSWERS,
    logicAttributes.ARRAY_FILTER_EXCLUSION,
    logicAttributes.ARRAY_FILTER,
    logicAttributes.ARRAY_FILTER_STYLE,
    logicAttributes.NUMBERS_ONLY_FOR_OTHER,
    logicAttributes.EXCLUSIVE_OPTIONS,
    logicAttributes.RANDOMIZATION_GROUP_NAME,
    logicAttributes.QUESTION_VALIDATION_EQUATION,
    logicAttributes.QUESTION_VALIDATION_TIP,
  ]
}

const otherSettings = () => {
  const otherAttributes = getOtherAttributes()
  return [
    otherAttributes.INSERT_PAGE_BREAK_IN_PRINTABLE_VIEW,
    otherAttributes.SPSS_EXPORT_SCALE_TYPE,
  ]
}

const inputSettings = () => {
  return []
}

const statisticsSettings = () => {
  const statisticsAttributes = getStatisticsAttributes()
  return [
    statisticsAttributes.SHOW_IN_STATISTICS,
    statisticsAttributes.SHOW_IN_PUBLIC_STATISTICS,
  ]
}

const timerSettings = () => {
  return Object.values(getTimerAttributes())
}

const themeOptionsSettings = () => {
  const themeAttributes = getThemeAttributes()
  return [
    themeAttributes.BUTTON_SIZE,
    themeAttributes.MAXIMUM_NUMBER_OF_BUTTONS_IN_A_ROW,
  ]
}

const fileMetaDataSettings = () => {
  return []
}

const locationSettings = () => {
  return []
}

const sliderSettings = () => {
  return []
}

export const getMultipleChoiceButtonsSettings = () => {
  return [
    {
      title: getQuestionAttributesTitles().SIMPLE,
      attributes: simpleSettings(),
    },
    {
      title: getQuestionAttributesTitles().GENERAL,
      attributes: generalSettings(),
    },
    {
      title: getQuestionAttributesTitles().DISPLAY,
      attributes: displaySettings(),
    },
    { title: getQuestionAttributesTitles().LOGIC, attributes: logicSettings() },
    { title: getQuestionAttributesTitles().OTHER, attributes: otherSettings() },
    { title: getQuestionAttributesTitles().INPUT, attributes: inputSettings() },
    {
      title: getQuestionAttributesTitles().STATISTICS,
      attributes: statisticsSettings(),
    },
    { title: getQuestionAttributesTitles().TIMER, attributes: timerSettings() },
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
  ]
}
