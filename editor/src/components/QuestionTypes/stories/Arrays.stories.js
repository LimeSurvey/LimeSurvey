import { within } from '@storybook/test'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { handleTests } from 'sbook/helpers/tests/handleTests'
import { ATTRIBUTES } from 'sbook/helpers/tests/attributes'

export default {
  title: 'QuestionTypes/Arrays',
}

/** PointChoice **/
const pointChoiceQuestion = mockQuestionType(getQuestionTypeInfo().ARRAY)

export const PointChoice = () => {
  return <QuestionPreview question={pointChoiceQuestion} />
}

PointChoice.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await handleTests(
    step,
    canvas,
    pointChoiceQuestion,
    getQuestionTypeInfo().ARRAY
  )
}

/** Numbers **/
const numbersQuestionAttributes = {
  ...ATTRIBUTES,
  display: {
    ...ATTRIBUTES.display,
    'minimum-value': {
      ...ATTRIBUTES.display['minimum-value'],
      value: true,
    },
    'maximum-value': {
      ...ATTRIBUTES.display['maximum-value'],
      value: true,
    },
  },
}

const numbersQuestion = mockQuestionType(getQuestionTypeInfo().ARRAY_NUMBERS)

export const Numbers = () => {
  return <QuestionPreview question={numbersQuestion} />
}

Numbers.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    pointChoiceQuestion,
    getQuestionTypeInfo().ARRAY_NUMBERS,
    numbersQuestionAttributes,
    8
  )
}

/** Texts **/
const textsQuestionAttributes = {
  ...ATTRIBUTES,
  input: {
    ...ATTRIBUTES.input,
    'maximum-characters': {
      ...ATTRIBUTES.input['maximum-characters'],
      value: true,
    },
  },
}

const textsQuestion = mockQuestionType(getQuestionTypeInfo().ARRAY_TEXT)

export const Texts = () => {
  return <QuestionPreview question={textsQuestion} />
}

Texts.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    textsQuestion,
    getQuestionTypeInfo().ARRAY_TEXT,
    textsQuestionAttributes,
    7
  )
}

/** ArrayByColumn **/
const arrayByColumnQuestion = mockQuestionType(
  getQuestionTypeInfo().ARRAY_COLUMN
)

export const ArrayByColumn = () => {
  return <QuestionPreview question={arrayByColumnQuestion} />
}

ArrayByColumn.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    arrayByColumnQuestion,
    getQuestionTypeInfo().ARRAY_COLUMN
  )
}

/** ArrayDualScale **/
const arrayDualScaleQuestionAttributes = {
  ...ATTRIBUTES,
  display: {
    ...ATTRIBUTES.display,
    'header-for-first-scale': {
      ...ATTRIBUTES.display['header-for-first-scale'],
      value: true,
    },
    'header-for-second-scale': {
      ...ATTRIBUTES.display['header-for-second-scale'],
      value: true,
    },
  },
}

const arrayDualScaleQuestion = mockQuestionType(
  getQuestionTypeInfo().ARRAY_DUAL_SCALE
)

export const ArrayDualScale = () => {
  return <QuestionPreview question={arrayDualScaleQuestion} />
}

ArrayDualScale.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    arrayDualScaleQuestion,
    getQuestionTypeInfo().ARRAY_DUAL_SCALE,
    arrayDualScaleQuestionAttributes,
    8
  )
}
