import Joi from 'joi'

export const QuestionHeaderSchema = Joi.object({
  title: Joi.string().required(),
})

export const TestValidation = (title) => {
  return QuestionHeaderSchema.validate({ title })
}
