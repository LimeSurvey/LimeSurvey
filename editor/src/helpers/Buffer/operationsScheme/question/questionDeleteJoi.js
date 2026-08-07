import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const questionDeleteJoi = Joi.object({
  entity: Joi.string().valid(Entities.question).required(),
  op: Joi.string().valid(Operations.delete).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
})
