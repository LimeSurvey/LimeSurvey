import { within } from '@storybook/test'

import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { handleTests } from 'sbook/helpers/tests/handleTests'
import { ATTRIBUTES } from 'sbook/helpers/tests/attributes'
import { mockQuestionTypeWithSettings } from '../../../sbook/helpers/fixtures/mockQuestionTypeWithSettings'

export default {
  title: 'QuestionTypes/Mask',
}

/** Rating **/
// const ratingQuestion = mockQuestionType(getQuestionTypeInfo().RATING)

// export const Rating = () => {
//   return <QuestionPreview question={ratingQuestion} />
// }

// Rating.play = async ({ canvasElement, step }) => {
//   const canvas = within(canvasElement)
//   await handleTests(step, canvas, ratingQuestion, getQuestionTypeInfo().RATING)
// }

/** Equation **/
const equationQuestionAttributes = {
  ...ATTRIBUTES,
  logic: {
    equation: {
      ...ATTRIBUTES.logic['equation'],
      value: true,
    },
  },
}

const equationQuestion = mockQuestionType(getQuestionTypeInfo().EQUATION)

export const Equation = () => {
  return <QuestionPreview question={equationQuestion} />
}

Equation.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await handleTests(
    step,
    canvas,
    equationQuestion,
    getQuestionTypeInfo().EQUATION,
    equationQuestionAttributes
  )
}

/** FileUpload **/
const fileUploadQuestionAttributes = {
  ...ATTRIBUTES,

  other: {
    ...ATTRIBUTES.other,
    'min-number-of-files': {
      ...ATTRIBUTES.other['min-number-of-files'],
      value: true,
    },
    'allowed-file-types': {
      ...ATTRIBUTES.other['allowed-file-types'],
      value: true,
    },
    'maximum-file-size-allowed': {
      ...ATTRIBUTES.other['maximum-file-size-allowed'],
      value: true,
    },
    'max-number-of-files': {
      ...ATTRIBUTES.other['max-number-of-files'],
      value: true,
    },
  },
  fileMetadata: {
    ...ATTRIBUTES.fileMetadata,
    'show-title': {
      ...ATTRIBUTES.fileMetadata['show-title'],
      value: true,
    },
    'show-comment': {
      ...ATTRIBUTES.fileMetadata['show-comment'],
      value: true,
    },
  },
}

const fileUploadQuestion = mockQuestionType(getQuestionTypeInfo().FILE_UPLOAD)

export const FileUpload = () => {
  return <QuestionPreview question={fileUploadQuestion} />
}

FileUpload.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    fileUploadQuestion,
    getQuestionTypeInfo().FILE_UPLOAD,
    fileUploadQuestionAttributes,
    12
  )
}

/** Gender **/
const genderQuestionAttributes = {
  ...ATTRIBUTES,
  display: {
    'display-type': {
      ...ATTRIBUTES.display['display-type'],
      value: true,
    },
  },
}

const genderQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().GENDER
)

export const Gender = () => {
  return (
    <QuestionPreview
      question={genderQuestion}
      surveySettings={genderQuestion.surveySettings}
    />
  )
}

Gender.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)

  await handleTests(
    step,
    canvas,
    genderQuestion,
    getQuestionTypeInfo().GENDER,
    genderQuestionAttributes
  )
}

/** MultipleNumericalInputs **/
const multipleNumericalInputsQuestionAttributes = {
  ...ATTRIBUTES,
  display: {
    'use-slider-layout': {
      ...ATTRIBUTES.display['use-slider-layout'],
      value: true,
    },
  },
  logic: {
    ...ATTRIBUTES.logic,
    'minimum-answers': {
      ...ATTRIBUTES.logic['minimum-answers'],
      value: true,
    },
    'maximum-answers': {
      ...ATTRIBUTES.logic['maximum-answers'],
      value: true,
    },
  },
  slider: {
    ...ATTRIBUTES.slider,
    orientation: {
      ...ATTRIBUTES.slider['orientation'],
      value: true,
    },
  },
}

const multipleNumericalInputsQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS
)

export const MultipleNumericalInput = () => {
  return <QuestionPreview question={multipleNumericalInputsQuestion} />
}

MultipleNumericalInput.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await handleTests(
    step,
    canvas,
    multipleNumericalInputsQuestion,
    getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS,
    multipleNumericalInputsQuestionAttributes,
    9
  )
}

/** RankingAdvanced **/
const rankingAdvancedQuestionAttributes = {
  ...ATTRIBUTES,
  display: {
    ...ATTRIBUTES.display,
    'same-height-for-all-answer-options': {
      ...ATTRIBUTES.display['same-height-for-all-answer-options'],
      value: true,
    },
    'same-height-for-lists': {
      ...ATTRIBUTES.display['same-height-for-lists'],
      value: true,
    },
    'visualization': {
      ...ATTRIBUTES.display['visualization'],
      value: true,
    },
  },
}

const rankingAdvancedQuestion = mockQuestionTypeWithSettings(
  getQuestionTypeInfo().RANKING_ADVANCED
)

export const RankingAdvanced = () => {
  return (
    <QuestionPreview
      question={rankingAdvancedQuestion}
      surveySettings={rankingAdvancedQuestion.surveySettings}
    />
  )
}

RankingAdvanced.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await handleTests(
    step,
    canvas,
    rankingAdvancedQuestion,
    getQuestionTypeInfo().RANKING_ADVANCED,
    rankingAdvancedQuestionAttributes,
    9
  )
}

/** TextDisplay **/
const textDisplayQuestion = mockQuestionType(getQuestionTypeInfo().TEXT_DISPLAY)

export const TextDisplay = () => {
  return <QuestionPreview question={textDisplayQuestion} />
}

TextDisplay.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await handleTests(
    step,
    canvas,
    textDisplayQuestion,
    getQuestionTypeInfo().TEXT_DISPLAY
  )
}

/** YesNo **/
const yesNoQuestionAttributes = {
  ...ATTRIBUTES,
  display: {
    ...ATTRIBUTES.display,
    'display-type': {
      ...ATTRIBUTES.display['display-type'],
      value: true,
    },
  },
}

const yesNoQuestion = mockQuestionTypeWithSettings(getQuestionTypeInfo().YES_NO)

export const YesNo = () => {
  return (
    <QuestionPreview
      question={yesNoQuestion}
      surveySettings={yesNoQuestion.surveySettings}
    />
  )
}

YesNo.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await handleTests(
    step,
    canvas,
    yesNoQuestion,
    getQuestionTypeInfo().YES_NO,
    yesNoQuestionAttributes,
    7
  )
}

/** BrowserDetection **/
const browserDetectionQuestionAttributes = {
  ...ATTRIBUTES,
  display: {
    ...ATTRIBUTES.display,
    'always-hide-this-question': {
      ...ATTRIBUTES.display['always-hide-this-question'],
      value: true,
    },
    'show-platform-information': {
      ...ATTRIBUTES.display['show-platform-information'],
      value: true,
    },
  },
}

const browserDetectionQuestion = mockQuestionType(
  getQuestionTypeInfo().BROWSER_DETECTION
)

export const BrowserDetection = () => {
  return <QuestionPreview question={browserDetectionQuestion} />
}

BrowserDetection.play = async ({ canvasElement, step }) => {
  const canvas = within(canvasElement)
  await handleTests(
    step,
    canvas,
    browserDetectionQuestion,
    getQuestionTypeInfo().BROWSER_DETECTION,
    browserDetectionQuestionAttributes,
    8
  )
}
