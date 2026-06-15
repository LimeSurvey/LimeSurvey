import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'

export default {
  title: 'QuestionTypes/Text',
}

const shortTextQuestion = mockQuestionType(getQuestionTypeInfo().SHORT_TEXT, {
  image: '',
})

export const ShortText = () => {
  return <QuestionPreview question={shortTextQuestion} />
}

const longTextQuestion = mockQuestionType(getQuestionTypeInfo().LONG_TEXT)

export const LongText = () => {
  return <QuestionPreview question={longTextQuestion} />
}

const multipleShortTextQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS
)

export const MultipleShortTexts = () => {
  return <QuestionPreview question={multipleShortTextQuestion} />
}
