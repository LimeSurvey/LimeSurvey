import Joi from 'joi'

import { Entities, Operations } from '../../../../helpers/Buffer'

export const surveyStatusUpdateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(), // Required ID (string or number)
  op: Joi.string().valid(Operations.update).required(), // Must be "update"
  entity: Joi.string().valid(Entities.surveyStatus).required(), // Must be "surveyStatus"
  error: Joi.boolean().optional(), // Optional error flag (boolean)
  props: Joi.object({
    anonymized: Joi.boolean().required(), // Required boolean
    activate: Joi.boolean(),
    deactivate: Joi.boolean(),
    expire: Joi.boolean(),
  }).required(), // `props` is required
})
