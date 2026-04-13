import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const surveyImportResponsesJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid(Operations.update).required(),
  entity: Joi.string().valid(Entities.importResponses).required(),
  error: Joi.boolean().optional(),
  props: Joi.object({
    preserveIDs: Joi.boolean().required(),
    timestamp: Joi.alternatives().try(Joi.string(), Joi.number()).optional(),
  }).required(),
})
