import Joi from 'joi'
import { escapeRegExp } from 'lodash'

import { NEW_OBJECT_ID_PREFIX } from 'helpers'

// A condition temp ID: "temp__12_1".
const CONDITION_TEMP_ID_REGEX = new RegExp(
  `^${escapeRegExp(NEW_OBJECT_ID_PREFIX)}\\d+_\\d+$`
)

const scenarioSchema = Joi.object({
  scid: Joi.number().required(),
  action: Joi.string().valid('deleteScenario').required(),
})

const conditionSchema = Joi.object({
  scid: Joi.number().required(),
  conditions: Joi.array()
    .items(
      Joi.object({
        // A temp cid when the condition was never saved: the delete only
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
  props: Joi.alternatives()
    .try(
      Joi.object({
        qid: Joi.alternatives().try(Joi.number(), Joi.string()).optional(),
        scenarios: Joi.array()
          .items(
            Joi.alternatives().try(scenarioSchema, conditionSchema).required()
          )
          .min(1)
          .required(),
      }),
      // Deletes every condition of the question.
      Joi.object({
        qid: Joi.alternatives().try(Joi.number(), Joi.string()).required(),
        action: Joi.string().valid('deleteAllConditions').required(),
      })
    )
    .required(),
})
