import Joi from 'joi'

import { Entities } from 'helpers/Buffer/Entities'
import { Operations } from 'helpers/Buffer/Operations'

export const questionGroupDeleteJoi = Joi.object({
  entity: Joi.string().valid(Entities.questionGroup).required(),
  op: Joi.string().valid(Operations.delete).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
})
