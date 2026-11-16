import classNames from 'classnames'
import { Form } from 'react-bootstrap'
import { format } from 'util'

const getAccessibleLabel = (code) => format(t('Subquestion code %s'), code)

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
  className = '',
}) => (
  <div className={classNames('question-code-container', className)}>
    {isSurveyActive ? (
      <div
        className="question-code-tag"
        style={isColumnTitle ? { marginLeft: '0px' } : undefined}
      >
        {code}
      </div>
    ) : (
      <input
        style={isColumnTitle ? { marginLeft: '0px' } : undefined}
        className="question-code-tag"
        type="text"
        autoComplete="off"
        aria-label={getAccessibleLabel(code)}
        value={code}
        onChange={onChange}
      />
    )}
  </div>
)
