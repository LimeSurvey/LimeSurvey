import Joi from 'joi'
import {
  conditionQuestionIdJoi,
  conditionTempIdJoi,
  questionConditionBaseJoi,
} from './questionConditionBaseJoi.js'

export const questionConditionCreateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid('create').required(),
  entity: Joi.string().valid('questionCondition').required(),
  qid: conditionQuestionIdJoi.required(),
  props: {
    qid: conditionQuestionIdJoi.required(),
    scenarios: Joi.array().items(
      Joi.object({
        scid: Joi.number().required(),
        conditions: Joi.array()
          .items(
            questionConditionBaseJoi.keys({
              action: Joi.string().valid('insertCondition').required(),
              tempId: conditionTempIdJoi.required(),
              cid: conditionTempIdJoi.required(),
              tempcids: Joi.array()
                .items(conditionTempIdJoi.required())
                .required(),
            })
          )
          .min(1)
          .required(),
      })
    ),
  },
})
