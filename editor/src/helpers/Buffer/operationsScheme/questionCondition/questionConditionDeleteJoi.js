import Joi from 'joi'

import { CONDITION_TEMP_ID_REGEX } from '../../../replaceTempIdsInObject'

const conditionSchema = Joi.object({
  scid: Joi.number().required(),
  conditions: Joi.array()
    .items(
      Joi.object({
        // A temp cid when the condition was never saved: The delete only
        // removes it from the buffer, or waits for the real cid when the
        // condition's create is being sent (see handleQuestionConditionOperation).
        cid: Joi.alternatives()
          .try(Joi.number(), Joi.string().pattern(CONDITION_TEMP_ID_REGEX))
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
  qid: Joi.alternatives().try(Joi.number(), Joi.string()).optional(),
  error: Joi.boolean().optional(),
  props: Joi.object({
    qid: Joi.alternatives().try(Joi.number(), Joi.string()).optional(),
    scenarios: Joi.array().items(conditionSchema.required()).min(1).required(),
  }).required(),
})
