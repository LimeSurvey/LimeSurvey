import Joi from 'joi'

import { Operations, Entities } from 'helpers/Buffer'

/**
 * Option keys depend on the theme, so any key is accepted here.
 * The server maps them against the theme manifest's optionAttributes.
 */
export const themeSettingUpdateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid(Operations.update).required(),
  entity: Joi.string().valid(Entities.themeSettings).required(),
  error: Joi.boolean().optional(),
  props: Joi.object({
    templateName: Joi.string().required(),
  })
    .unknown(true)
    .required(),
})
