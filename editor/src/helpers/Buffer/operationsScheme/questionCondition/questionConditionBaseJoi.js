import Joi from 'joi'
import {
  CONDITION_TEMP_ID_REGEX,
  NEW_OBJECT_ID_PREFIX,
} from 'helpers/constants'

// A question ID, or the temp ID of a question that isn't saved yet.
export const conditionQuestionIdJoi = Joi.alternatives().try(
  Joi.number(),
  Joi.string().pattern(new RegExp(`^${NEW_OBJECT_ID_PREFIX}\\d+$`))
)

// The temp ID of a condition that isn't saved yet.
export const conditionTempIdJoi = Joi.string().pattern(CONDITION_TEMP_ID_REGEX)

export const questionConditionBaseJoi = Joi.object({
  qid: conditionQuestionIdJoi.required(),
  cqid: conditionQuestionIdJoi.required(),
  cfieldname: Joi.string().required(),
  cquestions: Joi.string().required(),
  method: Joi.string()
    .valid('<', '>', '<=', '>=', '==', '!=', 'RX')
    .default('=='),
  value: Joi.string().allow('').trim(false),
  scenario: Joi.number().required(),
  editSourceTab: Joi.string()
    .pattern(/^#\w+$/)
    .required(),
  editTargetTab: Joi.string()
    .pattern(/^#\w+$/)
    .required(),
  csrctoken: Joi.string().optional(),
  canswers: Joi.alternatives().try(
    Joi.array().items(Joi.string()).min(1).required(),
    Joi.string().required()
  ),
  ConditionConst: Joi.string().allow('').trim(false),
  prevQuestionSGQA: Joi.string().optional(),
  ConditionRegexp: Joi.string().optional(),
  tokenAttr: Joi.string().optional(),
}).when('editTargetTab', {
  switch: [
    {
      is: '#CANSWERSTAB',
      then: Joi.object({
        canswers: Joi.required(),
        then: Joi.required(),
        otherwise: Joi.optional(),
      }).xor('canswers'),
    },
    {
      is: '#CONST',
      then: Joi.object({
        ConditionConst: Joi.string().allow('').trim(false),
      }).xor('ConditionConst'),
    },
    {
      is: '#PREVQUESTIONS',
      then: Joi.object({
        prevQuestionSGQA: Joi.required(),
      }).xor('prevQuestionSGQA'),
    },
    {
      is: '#REGEXP',
      then: Joi.object({
        ConditionRegexp: Joi.required(),
      }).xor('ConditionRegexp'),
    },
    {
      is: '#TOKENATTRS',
      then: Joi.object({
        tokenAttr: Joi.required(),
        prevQuestionSGQA: Joi.required(),
      }).and('tokenAttr', 'prevQuestionSGQA'),
    },
  ],
  otherwise: Joi.object().invalid(),
})
