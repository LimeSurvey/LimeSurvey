import { Form } from 'react-bootstrap'

export const SubquestionCodeErrorMessage = ({ errorMessage, maxWidth }) => (
  <div className={'text-wrap d-block '} style={{ maxWidth: maxWidth }}>
    <Form.Text className="text-danger question-code-tag-error">
      {errorMessage}
    </Form.Text>
  </div>
)

export const SubquestionCodeInput = ({
  isSurveyActive,
  code,
  onChange,
  isColumnTitle = false,
}) => (
  <div className="question-code-container">
    {isSurveyActive ? (
      <div
        className="question-code-tag"
        style={{ marginLeft: isColumnTitle ? '0px' : '20px' }}
      >
        {code}
      </div>
    ) : (
      <input
        style={{ marginLeft: isColumnTitle ? '0px' : '20px' }}
        className="question-code-tag"
        type="text"
        defaultValue={code}
        onChange={onChange}
      />
    )}
  </div>
)
