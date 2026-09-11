import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const questionGroupL10nUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.questionGroupL10n).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  props: Joi.object()
    .pattern(
      /^[a-zA-Z-]{2,}$/,
      Joi.object({
        groupName: Joi.string().allow('').optional(),
        description: Joi.string().allow('').optional(),
      }).or('groupName', 'description')
    )
    .required(),
})
