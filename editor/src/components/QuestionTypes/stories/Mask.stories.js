import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { mockQuestionType } from 'sbook/helpers/fixtures/mockQuestionType'
import { QuestionPreview } from 'sbook/helpers/fixtures/QuestionPreview'
import { mockQuestionTypeWithSettings } from '../../../sbook/helpers/fixtures/mockQuestionTypeWithSettings'

export default {
  title: 'QuestionTypes/Mask',
}

/** Rating **/
// const ratingQuestion = mockQuestionType(getQuestionTypeInfo().RATING)

// export const Rating = () => {
//   return <QuestionPreview question={ratingQuestion} />
// }

const equationQuestion = mockQuestionType(getQuestionTypeInfo().EQUATION)

export const Equation = () => {
  return <QuestionPreview question={equationQuestion} />
}

const fileUploadQuestion = mockQuestionType(getQuestionTypeInfo().FILE_UPLOAD)

export const FileUpload = () => {
  return <QuestionPreview question={fileUploadQuestion} />
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

const multipleNumericalInputsQuestion = mockQuestionType(
  getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS
)

export const MultipleNumericalInput = () => {
  return <QuestionPreview question={multipleNumericalInputsQuestion} />
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

/** TextDisplay **/
const textDisplayQuestion = mockQuestionType(getQuestionTypeInfo().TEXT_DISPLAY)

export const TextDisplay = () => {
  return <QuestionPreview question={textDisplayQuestion} />
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

/** BrowserDetection **/

const browserDetectionQuestion = mockQuestionType(
  getQuestionTypeInfo().BROWSER_DETECTION
)

export const BrowserDetection = () => {
  return <QuestionPreview question={browserDetectionQuestion} />
}
