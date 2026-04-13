import Joi from 'joi'

const scenarioSchema = Joi.object({
  scid: Joi.number().required(),
  action: Joi.string().valid('deleteScenario').required(),
})

const conditionSchema = Joi.object({
  scid: Joi.number().required(),
  conditions: Joi.array()
    .items(
      Joi.object({
        cid: Joi.number().required(),
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
  qid: Joi.number().optional(),
  props: Joi.object({
    qid: Joi.number().optional(),
    scenarios: Joi.array()
      .items(Joi.alternatives().try(scenarioSchema, conditionSchema).required())
      .min(1)
      .required(),
  }).required(),
})
