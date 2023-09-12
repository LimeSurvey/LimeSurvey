import Joi from 'joi'

export const QuestionGroupHeaderSchema = Joi.object({
  title: Joi.string().required(),
})

export const TestValidation = (title) => {
  return QuestionGroupHeaderSchema.validate({
    title,
  })
}
