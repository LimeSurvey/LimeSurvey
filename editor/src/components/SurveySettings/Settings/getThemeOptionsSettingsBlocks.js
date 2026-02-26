import {
  TYPES,
  handleDropdownType,
  handleButtonType,
  handleIconType,
  handleTextType,
  handleDurationType,
  handleTextareaType,
} from './themeOptionTypes'
import { handleColorPickerType } from './themeOptionTypes/handleColorPickerType'

export const getThemeOptions = (survey) => {
  // guard: ensure survey and themesettings exist
  const themesettings = (survey && survey.themesettings) || {}
  const keysToMap = Object.keys(themesettings)
  const options = {}

  keysToMap.forEach((keyPath) => {
    const attribute = survey.themesettings[keyPath]
    const category = survey.themesettings[keyPath].category

    if (!options[category]) {
      options[category] = { title: t(category), settings: {} }
    }

    // prepare a variable to hold the setting returned by handlers
    let setting
    if (attribute.type === TYPES.BUTTONS) {
      setting = handleButtonType(attribute, keyPath)
    } else if (attribute.type === TYPES.DROPDOWN) {
      setting = handleDropdownType(
        attribute,
        keyPath,
        null,
        attribute.hasFileUpload
      )
    } else if (attribute.type === TYPES.COLORPICKER) {
      setting = handleColorPickerType(attribute, keyPath)
    } else if (attribute.type === TYPES.ICON) {
      setting = handleIconType(attribute, keyPath)
    } else if (attribute.type === TYPES.TEXT) {
      setting = handleTextType(attribute, keyPath)
    } else if (attribute.type === TYPES.DURATION) {
      setting = handleDurationType(attribute, keyPath)
    } else if (attribute.type === TYPES.TEXTAREA) {
      setting = handleTextareaType(attribute, keyPath)
    }
    options[category].settings[keyPath] = setting
    options[category].settings[keyPath].formatDisplayValue = (value) => {
      return value.currentValue === 'inherit'
        ? value.parentValue
        : value.currentValue
    }
  })
  return options
}
