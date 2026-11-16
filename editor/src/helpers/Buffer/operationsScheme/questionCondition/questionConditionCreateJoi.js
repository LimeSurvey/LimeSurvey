import Joi from 'joi'
import { questionConditionBaseJoi } from './questionConditionBaseJoi.js'

export const questionConditionCreateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid('create').required(),
  entity: Joi.string().valid('questionCondition').required(),
  qid: Joi.number().required(),
  props: {
    qid: Joi.number().required(),
    scenarios: Joi.array().items(
      Joi.object({
        scid: Joi.number().required(),
        conditions: Joi.array()
          .items(
            questionConditionBaseJoi.keys({
              action: Joi.string().valid('insertCondition').required(),
              tempId: Joi.string()
                .pattern(/^temp__\d+_\d+$/)
                .required(),
              cid: Joi.string()
                .pattern(/^temp__\d+_\d+$/)
                .required(),
              tempcids: Joi.array()
                .items(
                  Joi.string()
                    .pattern(/^temp__\d+_\d+$/)
                    .required()
                )
                .required(),
            })
          )
          .min(1)
          .required(),
      })
    ),
  },
})
