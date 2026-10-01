import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const questionL10nUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.questionL10n).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  props: Joi.object()
    .pattern(
      /^[a-zA-Z-]{2,}$/,
      Joi.object({
        question: Joi.string().allow('').optional(),
        help: Joi.string().allow('').optional(),
      }).or('question', 'help')
    )
    .required(),
})
