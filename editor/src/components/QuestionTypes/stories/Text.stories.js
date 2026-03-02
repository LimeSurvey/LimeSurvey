import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { within, waitFor } from '@storybook/test'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { handleTests } from 'sbook/helpers/tests/handleTests'
import { ATTRIBUTES } from 'sbook/helpers/tests/attributes'
import { sleep } from 'helpers'

export default {
  title: 'QuestionTypes/Text',
}

/** ----------------------------------------------------------------------- **/
/** ShortText **/
const shortTextQuestionAttributes = {
  ...ATTRIBUTES,
  general: {
    ...ATTRIBUTES.general,
    'form-field-text': {
      ...ATTRIBUTES.general['form-field-text'],
      value: true,
    },
  },
  other: {
    ...ATTRIBUTES.other,
    'numbers-only': {
      ...ATTRIBUTES.other['numbers-only'],
      value: true,
    },
  },
  input: {
    ...ATTRIBUTES.input,
    'maximum-characters': {
      ...ATTRIBUTES.input['maximum-characters'],
      value: true,
    },
  },
}

const shortTextQuestion = mockQuestionType(getQuestionTypeInfo().SHORT_TEXT, {
  image: '',
})

export const ShortText = () => {
  return <QuestionPreview question={shortTextQuestion} />
}

ShortText.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await waitFor(() => canvas.getByTestId('text-question-answer-input'))
  await sleep(200)
  await handleTests(
    step,
    canvas,
    shortTextQuestion,
    getQuestionTypeInfo().SHORT_TEXT,
    shortTextQuestionAttributes,
    9
  )
}

/** ----------------------------------------------------------------------- **/
/**  LongText **/
const longTextQuestionAttributes = {
  ...ATTRIBUTES,
  general: {
    ...ATTRIBUTES.general,
    'form-field-text': {
      ...ATTRIBUTES.general['form-field-text'],
      value: true,
    },
  },

  input: {
    ...ATTRIBUTES.input,
    'maximum-characters': {
      ...ATTRIBUTES.input['maximum-characters'],
      value: true,
    },
  },
}

const longTextQuestion = mockQuestionType(getQuestionTypeInfo().LONG_TEXT)

export const LongText = () => {
  return <QuestionPreview question={longTextQuestion} />
}

LongText.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await waitFor(() => canvas.getByTestId('text-question-answer-input'))
  await sleep(200)
  await handleTests(
    step,
    canvas,
    longTextQuestion,
    getQuestionTypeInfo().LONG_TEXT,
    longTextQuestionAttributes,
    8
  )
}

/** ----------------------------------------------------------------------- **/
/**  MultipleShortText **/
const multipleShortTextQuestionAttributes = {
  ...ATTRIBUTES,
  general: {
    ...ATTRIBUTES.general,
    'form-field-text': {
      ...ATTRIBUTES.general['form-field-text'],
      value: true,
    },
  },
  display: {
    ...ATTRIBUTES.display,
    'input-on-demand': {
      ...ATTRIBUTES.display['input-on-demand'],
      value: true,
    },
  },
  input: {
    ...ATTRIBUTES.input,
    'maximum-characters': {
      ...ATTRIBUTES.input['maximum-characters'],
      value: true,
    },
  },
}

const multipleShortTextQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS
)

export const MultipleShortTexts = () => {
  return <QuestionPreview question={multipleShortTextQuestion} />
}

MultipleShortTexts.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await waitFor(() => canvas.getByTestId('question-content-editor'))
  await handleTests(
    step,
    canvas,
    multipleShortTextQuestion,
    getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS,
    multipleShortTextQuestionAttributes,
    9
  )
}
