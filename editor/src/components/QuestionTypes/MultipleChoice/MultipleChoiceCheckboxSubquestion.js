import { useState } from 'react'
import { Form, FormCheck } from 'react-bootstrap'
import classNames from 'classnames'

import { getQuestionTypeInfo } from '../getQuestionTypeInfo'
import { getCommentedCheckboxOptions } from 'helpers/options'
import { ContentEditor } from 'components/UIComponents'

export const MultipleChoiceCheckboxSubquestion = ({
  subQuestion,
  questionThemeName,
  value,
  commentedCheckbox,
}) => {
  const [isChecked, setIsChecked] = useState(false)
  const commentedCheckboxOptions = getCommentedCheckboxOptions()
  const showCommentInput =
    ((isChecked &&
      commentedCheckbox === commentedCheckboxOptions.CHECKED.value) ||
      (!isChecked &&
        commentedCheckbox === commentedCheckboxOptions.UNCHECKED.value) ||
      commentedCheckbox === commentedCheckboxOptions.ALWAYS.value) &&
    questionThemeName ===
      getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme

  return (
    <div className="d-flex align-items-center w-100 gap-4 multiple-choice-checkbox-subquestion">
      <Form.Group className="d-flex gap-4 align-items-center">
        <FormCheck
          value={subQuestion.qid}
          type={'checkbox'}
          checked={isChecked}
          name={`${subQuestion.qid}-multiple-choice-checkbox`}
          data-testid="multiple-choice-checkbox-input"
          onChange={() => {}}
          onClick={(e) => {
            e.stopPropagation()
            setIsChecked(!isChecked)
          }}
        />
        <ContentEditor
          className={classNames('multi-choice-checkbox-label choice', {
            'active-green-color': isChecked,
          })}
          data-placeholder="Edit text"
          value={value}
          disabled={true}
        />
      </Form.Group>
      {showCommentInput && (
        <div className="flex-1 w-50">
          <Form.Control
            onClick={(e) => {
              e.stopPropagation()
            }}
            value=""
            placeholder={st('Enter your comment here.')}
            rows={1}
            maxLength={Infinity}
            data-testid="multiple-choice-comment-input"
            defaultValue={subQuestion.assessmentComment}
            disabled={true}
          />
        </div>
      )}
    </div>
  )
}
