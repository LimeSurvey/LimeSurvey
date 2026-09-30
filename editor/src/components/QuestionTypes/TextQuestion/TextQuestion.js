import {
  getNotSupportedQuestionTypeInfo,
  getQuestionTypeInfo,
} from '../../QuestionTypes'

import './TextQuestion.scss'
import { LongTextAnswer } from './LongTextAnswer'
import { ShortTextAnswer } from './ShortTextAnswer'
import { NumericAnswer } from './NumericAnswer'
import { BrowserDetectionTextAnswer } from './BrowserDetectionTextAnswer'

/**
 * Text question rendering the answer component matching the question theme.
 * @param {Object} props
 * @param {Object} props.question Question with theme name and attributes.
 * @param {Object[]} props.values Answer values.
 * @param {Function} props.onValueChange Called with the new value and its key.
 */
export const TextQuestion = ({
  question: { questionThemeName, attributes },
  values = [],
  onValueChange = () => {},
}) => {
  const value = values?.[0] || {}

  const handleOnChange = (newValue) => {
    onValueChange(newValue, value.key)
  }

  return (
    <div className={'question-body-content'}>
      {questionThemeName === getQuestionTypeInfo().NUMERIC.theme && (
        <NumericAnswer value={value.value} onValueChange={handleOnChange} />
      )}
      {(questionThemeName === getQuestionTypeInfo().LONG_TEXT.theme ||
        questionThemeName ===
          getNotSupportedQuestionTypeInfo().HUGE_FREE_TEXT.theme) && (
        <LongTextAnswer
          attributes={attributes}
          value={value.value}
          onValueChange={handleOnChange}
        />
      )}
      {questionThemeName === getQuestionTypeInfo().SHORT_TEXT.theme && (
        <ShortTextAnswer
          attributes={attributes}
          value={value.value}
          onValueChange={handleOnChange}
        />
      )}
      {questionThemeName === getQuestionTypeInfo().BROWSER_DETECTION.theme && (
        <BrowserDetectionTextAnswer attributes={attributes} />
      )}
    </div>
  )
}
