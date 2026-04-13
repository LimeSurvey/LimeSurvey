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
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    getLogicAttributes().COMMENT_ONLY_WHEN,
    generalAttributes.LOGIC,
    getStatisticsAttributes().SHOW_IN_STATISTICS,
    getDisplayAttributes().IMAGE_SETTINGS,
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
    generalAttributes.INPUT_VALIDATION,
  ]
}

const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.IMAGE_SETTINGS,
    displayAttributes.CHOICE_COLUMN_WIDTH,
    displayAttributes.LABEL_FOR_OTHER_OPTIONS,
    displayAttributes.POSITION_FOR_OTHER_OPTION,
    displayAttributes.SUBQUESTION_TITLE,
    displayAttributes.SUBQUESTION_OPTIONS_ORDER,
    displayAttributes.KEEP_CODE_ORDER,
    displayAttributes.HIDE_TIP,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.CSS_CLASSES,
    displayAttributes.CONDITION_HELP_FOR_PRINTABLE_SURVEY,
    displayAttributes.TEXT_INPUT_COLUMNS,
  ]
}

const logicSettings = () => {
  return Object.values(getLogicAttributes())
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

const locationSettings = () => {
  return []
}

const sliderSettings = () => {
  return []
}

export const getMultipleChoiceWithCommentsSettings = () => {
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
