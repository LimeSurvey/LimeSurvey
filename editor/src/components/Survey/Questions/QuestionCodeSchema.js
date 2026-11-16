import Joi from 'joi'

export const QuestionCodeSchema = Joi.object({
  code: Joi.string().pattern(/^[A-Za-z0-9]+$/),
})

export const TestValidation = (code) => {
  return QuestionCodeSchema.validate({ code })
}
