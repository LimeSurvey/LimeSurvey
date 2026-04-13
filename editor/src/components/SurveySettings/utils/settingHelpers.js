import { getSettingValueFromSurvey } from 'helpers'
import { SurveySetting } from '../SurveySetting'

/**
 * Checks if a setting should be rendered based on its render condition
 * @param {Object} setting - The setting object
 * @param {Object} helperSettings - Helper settings object
 * @param {Object} survey - Survey object
 * @param {Object} globalStates - Global states object
 * @returns {boolean} - True if setting should be rendered
 */
export const checkRenderCondition = (
  setting,
  helperSettings,
  survey,
  globalStates
) => {
  const renderCondition = setting.condition?.render

  if (!renderCondition) {
    return true
  }

  const renderSettings = renderCondition.settings.map((condSetting) => {
    const value = condSetting.helperSetting
      ? helperSettings[condSetting.keyPath]
      : getSettingValueFromSurvey(survey, condSetting)

    return {
      setting: condSetting,
      value,
    }
  })

  return renderCondition.check(renderSettings, globalStates)
}

/**
 * Applies disable condition to a setting and updates its disabled state
 * @param {Object} setting - The setting object (will be mutated)
 * @param {Object} globalStates - Global states object
 */
export const applyDisableCondition = (setting, globalStates) => {
  if (!setting.disableCondition) {
    return
  }

  const shouldBeDisabled = setting.disableCondition.check(globalStates)
  setting.disabled = shouldBeDisabled
  setting.overlayMessage = setting.disableCondition.message
}

/**
 * Gets the raw value for a setting
 * @param {Object} setting - The setting object
 * @param {Object} helperSettings - Helper settings object
 * @param {Object} survey - Survey object
 * @returns {*} - The raw value
 */
export const getSettingValue = (setting, helperSettings, survey) => {
  return setting.helperSetting
    ? helperSettings[setting.keyPath]
    : getSettingValueFromSurvey(survey, setting)
}

/**
 * Formats the display value for a setting
 * @param {Object} setting - The setting object
 * @param {*} value - The raw value
 * @param {Object} globalStates - Global states object
 * @returns {*} - The formatted display value
 */
export const formatSettingValue = (setting, value, globalStates) => {
  return setting.formatDisplayValue
    ? setting.formatDisplayValue(value, globalStates)
    : value
}

/**
 * Gets the preview URL for a setting if applicable
 * @param {Object} setting - The setting object
 * @param {*} value - The raw value
 * @param {Object} globalStates - Global states object
 * @returns {string|null} - The preview URL or null
 */
export const getSettingPreviewUrl = (setting, value, globalStates) => {
  return setting.formatPreview
    ? setting.formatPreview(value, globalStates)
    : null
}

/**
 * Gets the component to use for rendering a setting
 * @param {Object} setting - The setting object
 * @returns {React.Component} - The component to use
 */
export const getSettingComponent = (setting) => {
  return setting.component || SurveySetting
}
