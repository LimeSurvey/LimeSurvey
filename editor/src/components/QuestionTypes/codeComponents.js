import classNames from 'classnames'
import { format } from 'util'

// Accessible name of a code input, naming the code and whether it belongs to an answer option or a subquestion
const getAccessibleLabel = (code, isAnswerCode) =>
  format(isAnswerCode ? t('Answer code %s') : t('Subquestion code %s'), code)

// Code of an answer option or subquestion: An input when editable, plain text otherwise
export const CodeInput = ({
  isEditable = true,
  isAnswerCode = false,
  code,
  onChange,
  isColumnTitle = false,
  className = '',
}) => (
  <div className={classNames('question-code-container', className)}>
    {isEditable ? (
      <input
        style={isColumnTitle ? { marginLeft: '0px' } : undefined}
        className="question-code-tag"
        type="text"
        autoComplete="off"
        aria-label={getAccessibleLabel(code, isAnswerCode)}
        value={code}
        onChange={onChange}
      />
    ) : (
      <div
        className="question-code-tag"
        style={isColumnTitle ? { marginLeft: '0px' } : undefined}
      >
        {code}
      </div>
    )}
  </div>
)
