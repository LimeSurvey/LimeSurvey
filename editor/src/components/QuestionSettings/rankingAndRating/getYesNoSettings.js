import { getQuestionAttributesTitles } from 'helpers'

import {
  getStatisticsAttributes,
  getOtherAttributes,
  getLogicAttributes,
  getDisplayAttributes,
  getGeneralAttributes,
  getTimerAttributes,
} from '../attributes'

const simpleSettings = () => {
  const generalAttributes = getGeneralAttributes()
  const displayAttributes = getDisplayAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    displayAttributes.DISPLAY_TYPE,
    generalAttributes.LOGIC,
    getStatisticsAttributes().SHOW_IN_STATISTICS,
    displayAttributes.IMAGE_SETTINGS,
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
  ]
}

const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.IMAGE_SETTINGS,
    displayAttributes.DISPLAY_TYPE,
    displayAttributes.HIDE_TIP,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.CSS_CLASSES,
  ]
}

const logicSettings = () => {
  return [getLogicAttributes().RANDOMIZATION_GROUP_NAME]
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
  return []
}

const fileMetaDataSettings = () => {
  return []
}

const sliderSettings = () => {
  return []
}

export const getYesNoSettings = () => {
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
      title: getQuestionAttributesTitles().SLIDER,
      attributes: sliderSettings(),
    },
  ]
}
