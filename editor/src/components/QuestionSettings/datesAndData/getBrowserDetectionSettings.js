import { getQuestionAttributesTitles } from 'helpers'

import {
  getStatisticsAttributes,
  getLogicAttributes,
  getDisplayAttributes,
  getGeneralAttributes,
  getTimerAttributes,
} from '../attributes'

/**
 * Attributes shown in the simple settings section of a browser detection question.
 * @returns {Object[]} Attribute definitions.
 */
const simpleSettings = () => {
  const generalAttributes = getGeneralAttributes()
  const displayAttributes = getDisplayAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    displayAttributes.IMAGE_SETTINGS,
    generalAttributes.LOGIC,
    displayAttributes.SHOW_PLATFORM_INFORMATION,
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
  ]
}

const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.IMAGE_SETTINGS,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.CSS_CLASSES,
    displayAttributes.SHOW_PLATFORM_INFORMATION,
    displayAttributes.PAGE_BREAK_IN_PRINTABLE_VIEW,
    displayAttributes.PLACEHOLDER_ANSWER,
  ]
}

const logicSettings = () => {
  const logicAttributes = getLogicAttributes()
  return [
    logicAttributes.RANDOMIZATION_GROUP_NAME,
    logicAttributes.QUESTION_VALIDATION_EQUATION,
    logicAttributes.QUESTION_VALIDATION_TIP,
  ]
}

const inputSettings = () => {
  return []
}

const statisticsSettings = () => {
  const statisticsAttributes = getStatisticsAttributes()
  return [
    statisticsAttributes.SHOW_IN_PUBLIC_STATISTICS,
    statisticsAttributes.SHOW_IN_STATISTICS,
  ]
}

const timerSettings = () => {
  return Object.values(getTimerAttributes())
}

const themeOptionsSettings = () => {
  return []
}

/**
 * Browser detection questions have no file metadata settings.
 * @returns {Object[]} Empty list.
 */
const fileMetaDataSettings = () => {
  return []
}

const sliderSettings = () => {
  return []
}

/**
 * Returns the settings sections of the browser detection question type.
 * @returns {{title: string, attributes: Object[]}[]} Settings sections.
 */
export const getBrowserDetectionSettings = () => {
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
      title: getQuestionAttributesTitles().SLIDER,
      attributes: sliderSettings(),
    },
  ]
}
