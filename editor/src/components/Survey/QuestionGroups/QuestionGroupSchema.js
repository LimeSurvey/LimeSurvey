import Joi from 'joi'

export const QuestionGroupHeaderSchema = Joi.object({})

export const TestValidation = () => {
  return QuestionGroupHeaderSchema.validate({})
}
