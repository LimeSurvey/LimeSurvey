import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { mockQuestionTypeWithSettings } from '../../../sbook/helpers/fixtures/mockQuestionTypeWithSettings'

export default {
  title: 'QuestionTypes/SingleChoice',
}

/** RadioList **/
const radioListQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO
)

export const RadioList = () => {
  return (
    <QuestionPreview
      question={radioListQuestion}
      surveySettings={radioListQuestion.surveySettings}
    />
  )
}

/** RadioListWithComments **/
const radioListWithCommentsQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT
)

export const RadioListWithComments = () => {
  return (
    <QuestionPreview
      question={radioListWithCommentsQuestion}
      surveySettings={radioListWithCommentsQuestion.surveySettings}
    />
  )
}

/** Dropdown **/
const dropdownQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN
)

export const Dropdown = () => {
  return (
    <QuestionPreview
      question={dropdownQuestion}
      surveySettings={dropdownQuestion.surveySettings}
    />
  )
}

/** SingleChoiceButtons **/
const singleChoiceButtonQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS
)

export const SingleChoiceButtons = () => {
  return (
    <QuestionPreview
      question={singleChoiceButtonQuestion}
      surveySettings={singleChoiceButtonQuestion.surveySettings}
    />
  )
}

/** SingleChoiceListSelect **/
const singleChoiceListSelectQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT
)

export const SingleChoiceListSelect = () => {
  return (
    <QuestionPreview
      question={singleChoiceListSelectQuestion}
      surveySettings={singleChoiceListSelectQuestion.surveySettings}
    />
  )
}

/** FivePointChoice **/
const fivePointChoiceQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE
)

export const FivePointChoice = () => {
  return (
    <QuestionPreview
      question={fivePointChoiceQuestion}
      surveySettings={fivePointChoiceQuestion.surveySettings}
    />
  )
}
