import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const subquestionCreateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid(Operations.create).required(),
  entity: Joi.string().valid(Entities.subquestion).required(),
  props: Joi.array()
    .items(
      Joi.object({
        tempId: Joi.string().required(), // tempId is now required
        sid: Joi.alternatives().try(Joi.string(), Joi.number()).optional(),
        qid: Joi.string().required(),
        gid: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        type: Joi.string().required(),
        scaleId: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
        sortOrder: Joi.alternatives()
          .try(Joi.string(), Joi.number())
          .required(),
        questionThemeName: Joi.string().required(),
        parentQid: Joi.alternatives()
          .try(Joi.string(), Joi.number())
          .required(),
        title: Joi.string().required(),
        preg: Joi.any().allow(null),
        other: Joi.boolean().optional(),
        mandatory: Joi.boolean().allow(null).required(),
        encrypted: Joi.boolean().optional(),
        moduleName: Joi.any().allow(null),
        sameDefault: Joi.any().optional(null),
        relevance: Joi.string().optional(),
        sameScript: Joi.any().allow(null).optional(),
        l10ns: Joi.object()
          .pattern(
            /^[a-zA-Z-]{2,}$/, // Matches valid language codes like "ar", "en"
            Joi.object({
              question: Joi.string().allow(''),
              language: Joi.string().required(),
            })
          )
          .required(),
        attributes: Joi.array().items(Joi.any()).optional(),
        answers: Joi.array().items(Joi.any()).optional(),
        subquestions: Joi.array().items(Joi.any()).optional(),
      })
    )
    .min(0)
    .required(),
})
