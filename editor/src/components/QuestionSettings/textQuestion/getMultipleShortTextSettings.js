import { getQuestionAttributesTitles } from 'helpers'

import {
  getTimerAttributes,
  getStatisticsAttributes,
  getOtherAttributes,
  getLogicAttributes,
  getDisplayAttributes,
  getGeneralAttributes,
} from '../attributes'

const simpleSettings = () => {
  const generalAttributes = getGeneralAttributes()
  const displayAttributes = getDisplayAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    generalAttributes.NUMBERS_ONLY,
    generalAttributes.MAX_CHARACTERS,
    displayAttributes.INPUT_ON_DEMAND,
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
    generalAttributes.MAX_CHARACTERS,
    generalAttributes.ENCRYPTED,
    generalAttributes.SAVE_AS_DEFAULT,
    generalAttributes.INPUT_VALIDATION,
    generalAttributes.NUMBERS_ONLY,
  ]
}

const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.IMAGE_SETTINGS,
    displayAttributes.INPUT_ON_DEMAND,
    displayAttributes.ANSWER_PREFIX,
    displayAttributes.ANSWER_SUFFIX,
    displayAttributes.SUBQUESTION_TITLE,
    displayAttributes.RANDOM_ORDER,
    displayAttributes.KEEP_CODE_ORDER,
    displayAttributes.HIDE_TIP,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.CSS_CLASSES,
    displayAttributes.TEXT_INPUT_COLUMNS,
    displayAttributes.TEXT_INPUT_BOX_SIZE,
    displayAttributes.DISPLAY_ROWS,
    displayAttributes.LABEL_WRAPPER_WIDTH,
  ]
}

const logicSettings = () => {
  return Object.values(getLogicAttributes())
}

const otherSettings = () => {
  return [getOtherAttributes().INSERT_PAGE_BREAK_IN_PRINTABLE_VIEW]
}

const inputSettings = () => {
  return []
}
const statisticsSettings = () => {
  return [getStatisticsAttributes().SHOW_IN_STATISTICS]
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

const locationSettings = () => {
  return []
}

const sliderSettings = () => {
  return []
}

export const getMultipleShortTextSettings = () => {
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
