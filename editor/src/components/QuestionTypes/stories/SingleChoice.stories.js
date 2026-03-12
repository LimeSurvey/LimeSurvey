import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { within } from '@storybook/test'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { handleTests } from 'sbook/helpers/tests/handleTests'
import { ATTRIBUTES } from 'sbook/helpers/tests/attributes'
import { mockQuestionTypeWithSettings } from '../../../sbook/helpers/fixtures/mockQuestionTypeWithSettings'

export default {
  title: 'QuestionTypes/SingleChoice',
}

const attributes = {
  ...ATTRIBUTES,
  display: {
    ...ATTRIBUTES.display,
    'answer-options-order': {
      ...ATTRIBUTES.display['answer-options-order'],
      value: true,
    },
  },
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

RadioList.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    radioListQuestion,
    getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO,
    attributes,
    7
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

Dropdown.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    dropdownQuestion,
    getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN,
    attributes,
    7
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

SingleChoiceButtons.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    singleChoiceButtonQuestion,
    getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS,
    attributes,
    7
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

SingleChoiceListSelect.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    singleChoiceListSelectQuestion,
    getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT,
    attributes,
    7
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

FivePointChoice.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    fivePointChoiceQuestion,
    getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE,
    attributes,
    7
  )
}
