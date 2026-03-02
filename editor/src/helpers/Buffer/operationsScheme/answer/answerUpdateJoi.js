import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const answerUpdateJoi = Joi.object({
  entity: Joi.string().valid(Entities.answer).required(),
  op: Joi.string().valid(Operations.update).required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  props: Joi.array()
    .items(
      Joi.object({
        tempId: Joi.alternatives().try(Joi.string(), Joi.number()).optional(),
        aid: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        code: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        sortOrder: Joi.alternatives()
          .try(Joi.string(), Joi.number())
          .required(),
        assessmentValue: Joi.alternatives()
          .try(Joi.string(), Joi.number())
          .required(),
        scaleId: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        l10ns: Joi.object()
          .pattern(
            /^[a-zA-Z-]{2,}$/,
            Joi.object({
              answer: Joi.alternatives()
                .try(Joi.string(), Joi.number())
                .required(),
              language: Joi.alternatives()
                .try(Joi.string(), Joi.number())
                .required(),
            })
          )
          .required(),
      })
    )
    .min(0)
    .required(),
})
