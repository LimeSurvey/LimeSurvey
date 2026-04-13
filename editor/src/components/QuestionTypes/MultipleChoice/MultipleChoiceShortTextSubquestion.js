import { ContentEditor, Input } from 'components/UIComponents'
import { Form } from 'react-bootstrap'

export const MultipleChoiceShortTextSubquestion = ({
  index,
  handleUpdateSubquestion,
  value,
}) => {
  return (
    <div className="w-100">
      <Form.Group className="d-flex w-100 gap-3 align-items-center">
        <Form.Label
          style={{ minWidth: 'fit-content' }}
          className="multi-choice-content-editor m-0"
        >
          <ContentEditor
            disabled={true}
            value={value}
            className="multi-choice-content-editor choice"
          />
        </Form.Label>
        <Input
          className="w-100"
          placeholder={st('Enter your comment here.')}
          dataTestId={'multiple-choice-comment-input'}
          onChange={(e) => handleUpdateSubquestion(e.target.value, index, true)}
        />
      </Form.Group>
    </div>
  )
}
