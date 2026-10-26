import Joi from 'joi'

import { THEME_OPTIONS_TYPES } from 'components/SurveySettings/Settings/themeOptionTypes/types'
import { Operations, Entities } from 'helpers/Buffer'

const getOptionValueJoi = (attribute = {}) => {
  switch (attribute.type) {
    case THEME_OPTIONS_TYPES.BUTTONS:
      return Joi.string().valid(
        ...String(attribute.options ?? '').split('|'),
        'inherit'
      )
    case THEME_OPTIONS_TYPES.TEXT:
    case THEME_OPTIONS_TYPES.TEXTAREA:
      return Joi.string().allow('')
    case THEME_OPTIONS_TYPES.DURATION:
      return Joi.alternatives().try(Joi.number(), Joi.string().allow(''))
    case THEME_OPTIONS_TYPES.DROPDOWN:
      // Uploaded images are not part of the declared options, and deleting one sends null.
      return Joi.string().allow(null)
    case THEME_OPTIONS_TYPES.COLORPICKER:
    case THEME_OPTIONS_TYPES.ICON:
      return Joi.string()
    default:
      return Joi.any()
  }
}

/**
 * Builds the themeSettings update schema from the theme manifest sent by the server
 * (survey.themesettings). Without a manifest, any option key is accepted.
 */
export const getThemeSettingUpdateJoi = (themesettings) => {
  const optionKeys = themesettings
    ? Object.fromEntries(
        Object.entries(themesettings).map(([key, attribute]) => [
          key,
          getOptionValueJoi(attribute).optional(),
        ])
      )
    : {}

  return Joi.object({
    id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
    op: Joi.string().valid(Operations.update).required(),
    entity: Joi.string().valid(Entities.themeSettings).required(),
    error: Joi.boolean().optional(),
    props: Joi.object({
      templateName: Joi.string().required(),
      ...optionKeys,
    })
      .unknown(!themesettings)
      .required(),
  })
}

export const themeSettingUpdateJoi = getThemeSettingUpdateJoi()
