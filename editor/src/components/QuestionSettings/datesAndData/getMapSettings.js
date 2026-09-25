import { Input } from 'components/UIComponents'
import { getQuestionAttributesTitles } from 'helpers'

import {
  getStatisticsAttributes,
  getOtherAttributes,
  getLogicAttributes,
  getDisplayAttributes,
  getGeneralAttributes,
  getTimerAttributes,
  getLocationAttributes,
} from '../attributes'

const simpleSettings = () => {
  const generalAttributes = getGeneralAttributes()
  const displayAttributes = getDisplayAttributes()
  return [
    generalAttributes.QUESTION_CODE,
    generalAttributes.QUESTION_TYPE,
    generalAttributes.MANDATORY,
    displayAttributes.IMAGE_SETTINGS,
    generalAttributes.LOGIC,
    getStatisticsAttributes().SHOW_IN_STATISTICS,
    getLocationAttributes().USE_MAPPING_SERVICE,
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
    displayAttributes.CSS_CLASSES,
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

const otherSettings = () => {
  return [getOtherAttributes().INSERT_PAGE_BREAK_IN_PRINTABLE_VIEW]
}

const inputSettings = () => {
  return []
}

const statisticsSettings = () => {
  const statisticsAttributes = getStatisticsAttributes()
  return [
    statisticsAttributes.SHOW_IN_PUBLIC_STATISTICS,
    statisticsAttributes.SHOW_IN_STATISTICS,
    statisticsAttributes.DISPLAY_MAP,
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
  return [
    ...Object.values(getLocationAttributes()),
    {
      component: Input,
      attributePath: 'attributes.location_mapwidth',
      props: {
        id: 'map-width',
        type: 'number',
        labelText: t('Map width'),
      },
    },
    {
      component: Input,
      attributePath: 'attributes.location_mapheight',
      props: {
        id: 'map-height',
        type: 'number',
        labelText: t('Map height'),
      },
    },
  ]
}

const sliderSettings = () => {
  return []
}

export const getMapSettings = () => {
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
