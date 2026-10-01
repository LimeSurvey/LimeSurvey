import Joi from 'joi'

import { Entities, Operations } from 'helpers/Buffer'
import { ACCESS_MODES } from 'helpers'

export const accessModeUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.accessMode).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  props: Joi.object({
    accessMode: Joi.string()
      .valid(...Object.values(ACCESS_MODES))
      .required(),
    action: Joi.string().optional().valid('K', 'D', 'A'), // related to tokens table => Optionally an active parameter can be passed besides the accessMode parameter, which may be K (keep), D (Drop) or A (Archive), depending on what we intend with the tokens table if switching to O
  }).required(),
})
