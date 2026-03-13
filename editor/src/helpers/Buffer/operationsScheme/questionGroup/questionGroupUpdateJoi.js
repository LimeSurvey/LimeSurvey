import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'
import { questionGroupL10nUpdateJoi } from './questionGroupL10nUpdateJoi'

export const questionGroupUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.questionGroup).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  props: Joi.object({
    questionGroup: Joi.object()
      .pattern(
        Joi.string(),
        Joi.alternatives().try(
          Joi.string().allow(''),
          Joi.number(),
          Joi.object(),
          Joi.array()
        )
      )
      .required(),
    questionGroupL10n: questionGroupL10nUpdateJoi.extract('props').required(),
  }).required(),
})
