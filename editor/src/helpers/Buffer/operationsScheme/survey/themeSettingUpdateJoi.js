import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const themeSettingUpdateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid(Operations.update).required(),
  entity: Joi.string().valid(Entities.themeSettings).required(),
  error: Joi.boolean().optional(),
  props: Joi.object({
    templateName: Joi.string().required(),
    font: Joi.string().optional(),
    cssframework: Joi.string().optional(),
    backgroundimagefile: Joi.string().optional(),
    brandlogofile: Joi.string().optional(),
    hideprivacyinfo: Joi.string().optional(),
    showpopups: Joi.string().optional(),
    showclearall: Joi.string().optional(),
    questionhelptextposition: Joi.string().optional(),
    fixnumauto: Joi.string().optional(),
    backgroundimage: Joi.string().optional(),
    brandlogo: Joi.string().optional(),
    cornerradius: Joi.string().optional(),
    container: Joi.string().optional(),
    zebrastriping: Joi.string().optional(),
    crosshover: Joi.string().optional(),
    fontcolor: Joi.string().optional(),
    bodybackgroundcolor: Joi.string().optional(),
    questionbackgroundcolor: Joi.string().optional(),
    themecolor: Joi.string().optional(),
    darkmode: Joi.string().optional(),
    checkicon: Joi.string().optional(),
  }).required(),
})
