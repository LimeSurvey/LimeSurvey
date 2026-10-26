import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const questionUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.question).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  props: Joi.object().pattern(Joi.string(), Joi.any()).required(),
})
