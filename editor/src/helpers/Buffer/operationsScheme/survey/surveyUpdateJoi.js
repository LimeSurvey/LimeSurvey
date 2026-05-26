import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const surveyUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.survey).required(),
  op: Joi.string().valid(Operations.update).required(),
  props: Joi.object().pattern(Joi.string(), Joi.any()).required(),
})
