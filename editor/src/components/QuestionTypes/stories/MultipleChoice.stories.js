import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { within } from '@storybook/test'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { ATTRIBUTES } from 'sbook/helpers/tests/attributes'
import { handleTests } from 'sbook/helpers/tests/handleTests'

export default {
  title: 'QuestionTypes/MultipleChoice',
}

/** Checkbox **/
const checkboxQuestion = mockQuestionType(getQuestionTypeInfo().MULTIPLE_CHOICE)

export const Checkbox = () => {
  return <QuestionPreview question={checkboxQuestion} />
}

Checkbox.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    checkboxQuestion,
    getQuestionTypeInfo().MULTIPLE_CHOICE
  )
}
/** WithComments **/
const withCommentsQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS
)

export const WithComments = () => {
  return <QuestionPreview question={withCommentsQuestion} />
}

WithComments.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    withCommentsQuestion,
    getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS,
    {
      logic: {
        ...ATTRIBUTES.logic,
        'comment-only-when': {
          ...ATTRIBUTES.logic['comment-only-when'],
          value: true,
        },
      },
    },
    7
  )
}

/** Buttons **/
const buttonsQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS
)

export const Buttons = () => {
  return <QuestionPreview question={buttonsQuestion} />
}

Buttons.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    buttonsQuestion,
    getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS
  )
}

/** Image Select **/
const imageSelectQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT
)

export const ImageSelect = () => {
  return <QuestionPreview question={imageSelectQuestion} />
}

ImageSelect.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    imageSelectQuestion,
    getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT
  )
}
