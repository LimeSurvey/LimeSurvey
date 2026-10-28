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

/**
 * Attributes shown in the simple settings section of a map question.
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
    getStatisticsAttributes().SHOW_IN_STATISTICS,
    getLocationAttributes().USE_MAPPING_SERVICE,
  ]
}

/**
 * Attributes shown in the general settings section of a map question.
 * @returns {Object[]} Attribute definitions.
 */
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

/**
 * Attributes shown in the display settings section of a map question.
 * @returns {Object[]} Attribute definitions.
 */
const displaySettings = () => {
  const displayAttributes = getDisplayAttributes()
  return [
    displayAttributes.IMAGE_SETTINGS,
    displayAttributes.HIDE_TIP,
    displayAttributes.ALWAYS_HIDE_THIS_QUESTION,
    displayAttributes.CSS_CLASSES,
  ]
}

/**
 * Attributes shown in the logic settings section of a map question.
 * @returns {Object[]} Attribute definitions.
 */
const logicSettings = () => {
  const logicAttributes = getLogicAttributes()
  return [
    logicAttributes.RANDOMIZATION_GROUP_NAME,
    logicAttributes.QUESTION_VALIDATION_EQUATION,
    logicAttributes.QUESTION_VALIDATION_TIP,
  ]
}

/**
 * Attributes shown in the other settings section of a map question.
 * @returns {Object[]} Attribute definitions.
 */
const otherSettings = () => {
  return [getOtherAttributes().INSERT_PAGE_BREAK_IN_PRINTABLE_VIEW]
}

/**
 * Map questions have no input settings.
 * @returns {Object[]} Empty list.
 */
const inputSettings = () => {
  return []
}

/**
 * Attributes shown in the statistics settings section of a map question.
 * @returns {Object[]} Attribute definitions.
 */
const statisticsSettings = () => {
  const statisticsAttributes = getStatisticsAttributes()
  return [
    statisticsAttributes.SHOW_IN_PUBLIC_STATISTICS,
    statisticsAttributes.SHOW_IN_STATISTICS,
    statisticsAttributes.DISPLAY_MAP,
  ]
}

/**
 * Attributes shown in the timer settings section of a map question.
 * @returns {Object[]} Attribute definitions.
 */
const timerSettings = () => {
  return Object.values(getTimerAttributes())
}

/**
 * Map questions have no theme option settings.
 * @returns {Object[]} Empty list.
 */
const themeOptionsSettings = () => {
  return []
}

/**
 * Map questions have no file metadata settings.
 * @returns {Object[]} Empty list.
 */
const fileMetaDataSettings = () => {
  return []
}

/**
 * Attributes shown in the location settings section of a map question.
 * @returns {Object[]} Attribute definitions.
 */
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

/**
 * Map questions have no slider settings.
 * @returns {Object[]} Empty list.
 */
const sliderSettings = () => {
  return []
}

/**
 * Returns the settings sections of the map question type.
 * @returns {{title: string, attributes: Object[]}[]} Settings sections.
 */
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
