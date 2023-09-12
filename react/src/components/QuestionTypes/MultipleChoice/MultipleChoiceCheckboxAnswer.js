import { Form, FormCheck } from 'react-bootstrap'
import ContentEditable from 'react-contenteditable'

import { QuestionTypeInfo } from '../QuestionTypeInfo'
import { useState } from 'react'

export const MultipleChoiceCheckboxAnswer = ({
  answer,
  questionThemeName,
  index,
  handleUpdateAnswer,
  handleFocus = () => {},
  handleBlur = () => {},
  value,
}) => {
  const [isChecked, setIsChecked] = useState(false)

  const onFocus = () => {
    handleFocus()
  }

  const onBlur = () => {
    handleBlur()
  }

  return (
    <div className="d-flex gap-2 align-items-center w-100 d-flex justify-content-between">
      <FormCheck
        checked={isChecked}
        value={answer.code}
        type={'checkbox'}
        className="pointer-events-none"
        label={
          <ContentEditable
            onFocus={onFocus}
            onBlur={onBlur}
            data-placeholder="Edit answer"
            html={value}
            onChange={(e) => handleUpdateAnswer(e.target.value, index)}
            className="multi-choice-form-label"
          />
        }
        name={`${answer.qid}-multiple-choice-checkbox-answer`}
        data-testid="multiple-choice-checkbox-answer-input"
        onChange={(e) => setIsChecked(e.target.checked)}
      />
      {questionThemeName ===
        QuestionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.theme && (
        <div style={{ width: '50%' }}>
          <Form.Group>
            <Form.Control
              placeholder="Enter your answer here."
              rows={1}
              maxLength={Infinity}
              data-testid="multiple-choice-comment-answer-input"
              value={answer?.assessmentComment}
              onChange={(e) => handleUpdateAnswer(e.target.value, index, true)}
              disabled={!isChecked}
            />
          </Form.Group>
        </div>
      )}
    </div>
  )
}
