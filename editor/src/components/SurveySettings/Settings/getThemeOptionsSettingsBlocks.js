import {
  THEME_OPTIONS_TYPES,
  handleDropdownType,
  handleButtonType,
  handleIconType,
  handleTextType,
  handleDurationType,
  handleTextareaType,
} from './themeOptionTypes'
import { handleColorPickerType } from './themeOptionTypes/handleColorPickerType'

const typeHandlers = {
  [THEME_OPTIONS_TYPES.BUTTONS]: handleButtonType,
  [THEME_OPTIONS_TYPES.DROPDOWN]: (attribute, keyPath) =>
    handleDropdownType(attribute, keyPath, attribute.hasFileUpload),
  [THEME_OPTIONS_TYPES.COLORPICKER]: handleColorPickerType,
  [THEME_OPTIONS_TYPES.ICON]: handleIconType,
  [THEME_OPTIONS_TYPES.TEXT]: handleTextType,
  [THEME_OPTIONS_TYPES.DURATION]: handleDurationType,
  [THEME_OPTIONS_TYPES.TEXTAREA]: handleTextareaType,
}

export const getThemeOptions = (survey) => {
  // guard: ensure survey and themesettings exist
  const themesettings = (survey && survey.themesettings) || {}
  const keysToMap = Object.keys(themesettings)
  const options = {}

  keysToMap.forEach((keyPath) => {
    const attribute = survey.themesettings[keyPath]
    const category = survey.themesettings[keyPath].category
    const handler = typeHandlers[attribute.type]

    if (!handler) {
      return
    }

    if (!options[category]) {
      options[category] = { title: t(category), settings: {} }
    }

    const setting = handler(attribute, keyPath)
    setting.attribute = attribute
    options[category].settings[keyPath] = setting
    options[category].settings[keyPath].formatDisplayValue = (value) => {
      return value.currentValue === 'inherit'
        ? value.parentValue
        : value.currentValue
    }
  })
  return options
}
