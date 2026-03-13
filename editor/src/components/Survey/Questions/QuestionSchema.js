import Joi from 'joi'

export const QuestionHeaderSchema = Joi.object({})

export const TestValidation = () => {
  return QuestionHeaderSchema.validate({})
}
