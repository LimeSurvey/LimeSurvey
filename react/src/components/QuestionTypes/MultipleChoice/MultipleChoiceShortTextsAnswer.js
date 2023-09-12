import { ContentEditor, Input } from 'components/UIComponents'
import { Form } from 'react-bootstrap'

export const MultipleChoiceShortTextsAnswer = ({
  index,
  handleUpdateAnswer,
  handleFocus = () => {},
  handleBlur = () => {},
  highestWidth,
  value,
}) => {
  const onFocus = () => {
    handleFocus()
  }

  const onBlur = () => {
    handleBlur()
  }

  return (
    <div className="w-100" onFocus={onFocus} onBlur={onBlur}>
      <Form.Group className="d-flex gap-3 align-items-center w-100 d-flex justify-content-between">
        <Form.Label className="multi-choice-form-label text-end m-0">
          <h6 className="m-0">
            <ContentEditor
              style={{ width: highestWidth }}
              disabled={true}
              value={value}
            />
          </h6>
        </Form.Label>
        <Input
          className="flex-1 w-100"
          placeholder="Enter your comment here."
          dataTestId={'multiple-choice-comment-answer-input'}
          onChange={(e) => handleUpdateAnswer(e.target.value, index, true)}
        />
      </Form.Group>
    </div>
  )
}
