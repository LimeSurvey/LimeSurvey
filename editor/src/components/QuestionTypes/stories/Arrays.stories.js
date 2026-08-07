import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'

export default {
  title: 'QuestionTypes/Arrays',
}

/** PointChoice **/
const pointChoiceQuestion = mockQuestionType(getQuestionTypeInfo().ARRAY)

export const PointChoice = () => {
  return <QuestionPreview question={pointChoiceQuestion} />
}

const numbersQuestion = mockQuestionType(getQuestionTypeInfo().ARRAY_NUMBERS)

export const Numbers = () => {
  return <QuestionPreview question={numbersQuestion} />
}

const textsQuestion = mockQuestionType(getQuestionTypeInfo().ARRAY_TEXT)

export const Texts = () => {
  return <QuestionPreview question={textsQuestion} />
}

/** ArrayByColumn **/
const arrayByColumnQuestion = mockQuestionType(
  getQuestionTypeInfo().ARRAY_COLUMN
)

export const ArrayByColumn = () => {
  return <QuestionPreview question={arrayByColumnQuestion} />
}

const arrayDualScaleQuestion = mockQuestionType(
  getQuestionTypeInfo().ARRAY_DUAL_SCALE
)

export const ArrayDualScale = () => {
  return <QuestionPreview question={arrayDualScaleQuestion} />
}
