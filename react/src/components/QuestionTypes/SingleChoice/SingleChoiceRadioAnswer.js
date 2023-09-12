import { FormCheck } from 'react-bootstrap'
import ContentEditable from 'react-contenteditable'

export const SingleChoiceRadioAnswer = ({
  answer: { code } = {},
  qid,
  value,
  index,
  handleUpdateAnswer,
  handleFocus = () => {},
  handleBlur = () => {},
}) => {
  return (
    <FormCheck
      value={code}
      type={'radio'}
      className="pointer-events-none"
      label={
        <ContentEditable
          onFocus={handleFocus}
          onBlur={handleBlur}
          data-placeholder="Edit answer"
          html={value}
          onChange={(e) => handleUpdateAnswer(e.target.value, index)}
        />
      }
      name={`${qid}-radio-list`}
      data-testid="single-choice-radio-answer"
    />
  )
}
