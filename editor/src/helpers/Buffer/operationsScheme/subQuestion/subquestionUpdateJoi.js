import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const subquestionUpdateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid(Operations.update).required(),
  entity: Joi.string().valid(Entities.subquestion).required(),
  props: Joi.array()
    .items(
      Joi.object({
        qid: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        tempId: Joi.alternatives().try(Joi.string(), Joi.number()).optional(), // Optional tempId
        parentQid: Joi.alternatives()
          .try(Joi.string(), Joi.number())
          .optional(),
        sid: Joi.alternatives().try(Joi.string(), Joi.number()).optional(),
        type: Joi.string().required(),
        sortOrder: Joi.alternatives()
          .try(Joi.string(), Joi.number())
          .required(),
        title: Joi.string().required(),
        preg: Joi.any().allow(null),
        other: Joi.boolean().optional(),
        mandatory: Joi.boolean().allow(null),
        encrypted: Joi.boolean().optional(),
        questionOrder: Joi.alternatives()
          .try(Joi.string(), Joi.number())
          .optional(),
        scaleId: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        sameDefault: Joi.boolean().allow(null),
        questionThemeName: Joi.string().allow(null).optional(),
        moduleName: Joi.string().allow(null).optional(),
        gid: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        relevance: Joi.string().required(),
        sameScript: Joi.boolean().allow(null).optional(),
        l10ns: Joi.object()
          .pattern(
            /^[a-zA-Z-]{2,}$/, // Language keys like "ar"
            Joi.object({
              id: Joi.alternatives().try(Joi.string(), Joi.number()).optional(),
              qid: Joi.alternatives()
                .try(Joi.string(), Joi.number())
                .optional(),
              question: Joi.string().allow(''),
              help: Joi.string().allow(''),
              script: Joi.any().allow(null),
              language: Joi.string().required(),
            })
          )
          .required(),
        attributes: Joi.array().items(Joi.any()).required(),
        answers: Joi.array().items(Joi.any()).required(),
        subquestions: Joi.array().items(Joi.any()).optional(),
      })
    )
    .min(0)
    .required(),
})
