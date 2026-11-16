import Joi from 'joi'
import { questionConditionBaseJoi } from './questionConditionBaseJoi.js'

const actionConditionTypes = ['insertCondition', 'updateCondition']

const conditionActionPropsSchema = {
  qid: Joi.number().required(),
  scenarios: Joi.array().items(
    Joi.object({
      scid: Joi.number().required(),
      conditions: Joi.array()
        .items(
          questionConditionBaseJoi.keys({
            action: Joi.string()
              .valid(...actionConditionTypes)
              .required(),
            cid: Joi.alternatives()
              .try(Joi.number(), Joi.string().pattern(/^temp__\d+_\d+$/))
              .required(),
            tempId: Joi.string()
              .pattern(/^temp__\d+_\d+$/)
              .optional(),
            tempcids: Joi.array().items(Joi.string()).optional(),
          })
        )
        .min(1)
        .required(),
    })
  ),
}

const conditionScriptPropsSchema = Joi.object({
  qid: Joi.number().required(),
  action: Joi.string().valid('conditionScript').required(),
  script: Joi.string().required(),
})

export const questionConditionUpdateJoi = Joi.object({
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  op: Joi.string().valid('update').required(),
  entity: Joi.string().valid('questionCondition').required(),
  qid: Joi.number().required(),
  props: Joi.alternatives().conditional('props.action', {
    switch: [
      {
        is: Joi.valid(...actionConditionTypes),
        then: conditionActionPropsSchema,
      },
      {
        is: 'conditionScript',
        then: conditionScriptPropsSchema,
      },
    ],
    otherwise: Joi.forbidden(),
  }),
})
