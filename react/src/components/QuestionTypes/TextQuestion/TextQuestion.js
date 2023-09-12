import { useEffect, useState } from 'react'

import { MINIMUM_INPUT_WIDTH_PERCENT } from 'helpers'
import { QuestionTypeInfo } from '../../QuestionTypes'

import './TextQuestion.scss'
import { LongTextAnswer } from './LongTextAnswer'
import { ShortTextAnswer } from './ShortTextAnswer'
import { BrowserDetectionTextAnswer } from './BrowserDetectionTextAnswer'

export const TextQuestion = ({
  question: {
    questionThemeName,
    attributes: { text_input_width = {}, form_field_text = {} } = {},
    attributes,
    answerExample,
  },
  handleUpdate,
}) => {
  const [, setAnswer] = useState(form_field_text?.value)

  useEffect(() => {
    if (form_field_text?.value !== undefined) {
      setAnswer(form_field_text?.value)
    }
  }, [form_field_text.value])

  const handleAnswerUpdate = (answerExample) => {
    handleUpdate({ answerExample })
  }

  return (
    <div
      style={{
        width:
          Math.max(text_input_width.value, MINIMUM_INPUT_WIDTH_PERCENT) + '%' ||
          '100%',
      }}
      className={'question-body-content mb-3'}
    >
      {questionThemeName === QuestionTypeInfo.LONG_TEXT.theme && (
        <LongTextAnswer
          answer={answerExample}
          setAnswer={handleAnswerUpdate}
          attributes={attributes}
        />
      )}
      {questionThemeName === QuestionTypeInfo.SHORT_TEXT.theme && (
        <ShortTextAnswer
          answer={answerExample}
          setAnswer={handleAnswerUpdate}
          attributes={attributes}
        />
      )}
      {questionThemeName === QuestionTypeInfo.BROWSER_DETECTION.theme && (
        <BrowserDetectionTextAnswer attributes={attributes} />
      )}
    </div>
  )
}
