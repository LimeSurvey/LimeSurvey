import Joi from 'joi'
import { CONDITION_TEMP_ID_REGEX } from '../../../replaceTempIdsInObject'
import { questionConditionBaseJoi } from './questionConditionBaseJoi.js'

export const questionConditionCreateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid('create').required(),
  entity: Joi.string().valid('questionCondition').required(),
  qid: Joi.alternatives().try(Joi.number(), Joi.string()).required(),
  error: Joi.boolean().optional(),
  props: {
    qid: Joi.alternatives().try(Joi.number(), Joi.string()).required(),
    scenarios: Joi.array().items(
      Joi.object({
        scid: Joi.number().required(),
        conditions: Joi.array()
          .items(
            questionConditionBaseJoi.keys({
              action: Joi.string().valid('insertCondition').required(),
              tempId: Joi.string().pattern(CONDITION_TEMP_ID_REGEX).required(),
              cid: Joi.string().pattern(CONDITION_TEMP_ID_REGEX).required(),
              tempcids: Joi.array()
                .items(Joi.string().pattern(CONDITION_TEMP_ID_REGEX).required())
                .required(),
            })
          )
          .min(1)
          .required(),
      })
    ),
  },
})
