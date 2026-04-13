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
  const logicAttributes = getLogicAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    getDisplayAttributes().IMAGE_SETTINGS,
    generalAttributes.LOGIC,
    logicAttributes.MINIMUM_ANSWERS,
    logicAttributes.MAXIMUM_ANSWERS,
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
  ]
}

const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.IMAGE_SETTINGS,
    displayAttributes.HIDE_TIP,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.ANSWER_OPTIONS_ORDER,
    displayAttributes.KEEP_CODE_ORDER,
    displayAttributes.SAME_HEIGHT_FOR_ALL_ANSWER_OPTIONS,
    displayAttributes.SAME_HEIGHT_FOR_LIST,
    displayAttributes.SHOW_JAVASCRIPT_ALERT,
    displayAttributes.SHOW_HANDLE,
    displayAttributes.SHOW_NUMBER,
    displayAttributes.WITHOUT_REORDER,
    displayAttributes.CSS_CLASSES,
    displayAttributes.VISUALIZATION,
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
    logicAttributes.RANDOMIZATION_GROUP_NAME,
  ]
}

const otherSettings = () => {
  const otherAttributes = getOtherAttributes()
  return [
    otherAttributes.CHOICE_HEADER,
    otherAttributes.RANK_HEADER,
    otherAttributes.INSERT_PAGE_BREAK_IN_PRINTABLE_VIEW,
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

export const getRankingAdvancedSettings = () => {
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
