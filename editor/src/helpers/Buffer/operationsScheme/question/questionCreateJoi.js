import Joi from 'joi'

import { questionUpdateJoi } from './questionUpdateJoi'
import { questionL10nUpdateJoi } from './questionL10nUpdateJoi'
import { questionAttributeUpdateJoi } from './questionAttributeUpdateJoi'
import { answerCreateJoi } from '../answer/answerCreateJoi'
import { subquestionCreateJoi } from '../subQuestion/subquestionCreateJoi'

export const questionCreateJoi = Joi.object({
  entity: Joi.string().valid('question').required(),
  op: Joi.string().valid('create').required(),
  id: Joi.alternatives().try(Joi.string(), Joi.number()).required(),
  props: Joi.object({
    question: questionUpdateJoi.extract('props').required(),
    questionL10n: questionL10nUpdateJoi.extract('props').required(),
    attributes: questionAttributeUpdateJoi.extract('props').optional(),
    answers: answerCreateJoi.extract('props').optional(),
    subquestions: subquestionCreateJoi.extract('props').optional(),
  }).required(),
})
