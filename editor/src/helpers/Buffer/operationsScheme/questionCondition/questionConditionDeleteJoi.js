import Joi from 'joi'
import {
  conditionQuestionIdJoi,
  conditionTempIdJoi,
} from './questionConditionBaseJoi.js'

const scenarioSchema = Joi.object({
  scid: Joi.number().required(),
  action: Joi.string().valid('deleteScenario').required(),
})

const conditionSchema = Joi.object({
  scid: Joi.number().required(),
  conditions: Joi.array()
    .items(
      Joi.object({
        cid: Joi.alternatives()
          .try(Joi.number(), conditionTempIdJoi)
          .required(),
        action: Joi.string().valid('deleteCondition').required(),
      })
    )
    .min(1)
    .required(),
})

export const questionConditionDeleteJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid('delete').required(),
  entity: Joi.string().valid('questionCondition').required(),
  qid: conditionQuestionIdJoi.optional(),
  props: Joi.object({
    qid: conditionQuestionIdJoi.optional(),
    scenarios: Joi.array()
      .items(Joi.alternatives().try(scenarioSchema, conditionSchema).required())
      .min(1)
      .required(),
  }).required(),
})
