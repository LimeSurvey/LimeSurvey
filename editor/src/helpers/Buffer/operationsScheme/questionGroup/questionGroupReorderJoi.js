import Joi from 'joi'

import { Entities } from 'helpers/Buffer/Entities'
import { Operations } from 'helpers/Buffer/Operations'

const idJoi = Joi.alternatives().try(Joi.string(), Joi.number())

// Props are keyed by group id, each group holding its questions keyed by question id.
export const questionGroupReorderJoi = Joi.object({
  entity: Joi.string().valid(Entities.questionGroupReorder).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.any().optional(),
  props: Joi.object()
    .pattern(
      idJoi,
      Joi.object({
        sortOrder: Joi.number().required(),
        questions: Joi.object()
          .pattern(idJoi, Joi.object({ sortOrder: Joi.number().required() }))
          .required(),
      })
    )
    .required(),
})
