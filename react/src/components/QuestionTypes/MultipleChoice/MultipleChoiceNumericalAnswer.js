import { Direction } from 'react-range'
import { Form } from 'react-bootstrap'

import { ContentEditor, Input, InputRange } from 'components/UIComponents'

export const MultipleChoiceNumericalAnswer = ({
  answer,
  index,
  handleUpdateAnswer,
  handleFocus = () => {},
  handleBlur = () => {},
  highestWidth,
  useSlider,
  orientation,
  value,
}) => {
  const onFocus = () => {
    handleFocus()
  }

  const onBlur = () => {
    handleBlur()
  }

  return (
    <div className="w-100" onFocus={onFocus}>
      <Form.Group className="d-flex gap-3 align-items-center w-100 d-flex justify-content-between">
        <Form.Label className="multi-choice-form-label text-end m-0">
          <h6 className="m-0">
            <ContentEditor
              style={{ width: highestWidth }}
              onBlur={onBlur}
              disabled={true}
              value={value}
            />
          </h6>
        </Form.Label>
        {useSlider ? (
          <InputRange
            showInput={false}
            direction={orientation ? orientation : Direction.Right}
          />
        ) : (
          <Input
            className="flex-1 w-100"
            placeholder="Enter your comment here."
            dataTestId={'multiple-choice-comment-answer-input'}
            onChange={(e) => handleUpdateAnswer(e.target.value, index, true)}
            type="number"
          />
        )}
      </Form.Group>
    </div>
  )
}
