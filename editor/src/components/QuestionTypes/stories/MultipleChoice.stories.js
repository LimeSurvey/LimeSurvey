import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'

export default {
  title: 'QuestionTypes/MultipleChoice',
}

/** Checkbox **/
const checkboxQuestion = mockQuestionType(getQuestionTypeInfo().MULTIPLE_CHOICE)

export const Checkbox = () => {
  return <QuestionPreview question={checkboxQuestion} />
}

/** WithComments **/
const withCommentsQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS
)

export const WithComments = () => {
  return <QuestionPreview question={withCommentsQuestion} />
}

/** Buttons **/
const buttonsQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS
)

export const Buttons = () => {
  return <QuestionPreview question={buttonsQuestion} />
}

/** Image Select **/
const imageSelectQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT
)

export const ImageSelect = () => {
  return <QuestionPreview question={imageSelectQuestion} />
}
