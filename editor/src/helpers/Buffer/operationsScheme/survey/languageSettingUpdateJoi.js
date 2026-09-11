import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const languageSettingUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.languageSetting).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.any().valid(null).required(),
  props: Joi.object()
    .pattern(
      Joi.string(),
      Joi.object().pattern(Joi.string().allow(''), Joi.any())
    )
    .required(),
})
