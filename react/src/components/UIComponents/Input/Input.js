import { Form } from 'react-bootstrap'
import NumericInput from './NumericInput'

export const Input = ({
  value = '',
  dataTestId,
  onChange = () => {},
  onBlur = () => {},
  inputRef = { current: { value: '' } },
  type = 'text',
  placeholder = '',
  id = '',
  Icon,
  leftIcons,
  labelText,
  paddinRight = '40px',
  paddingLeft = '40px',
  disabled = false,
  min = -Infinity,
  max = Infinity,
  allowEmpty = false,
  className = '',
  style = {},
}) => {
  return (
    <Form.Group
      style={{
        position: Icon && 'relative',
        ...style,
      }}
      className={`qe-input-group ${className}`}
    >
      {leftIcons && typeof leftIcons === 'string' && (
        <img
          style={{ left: 0, right: '100%' }}
          src={leftIcons}
          className="qe-input-icon-left"
          alt="input icon"
        />
      )}
      {leftIcons && typeof leftIcons !== 'string' && (
        <span className="qe-input-icon-left">{leftIcons}</span>
      )}
      {labelText && <Form.Label>{labelText}</Form.Label>}
      {type !== 'number' ? (
        <Form.Control
          disabled={disabled}
          id={id}
          ref={inputRef}
          data-testid={dataTestId}
          placeholder={placeholder}
          onChange={(event) => onChange(event)}
          defaultValue={value}
          type={type}
          onBlur={onBlur}
          style={{
            paddingRight: Icon && paddinRight,
            paddingLeft: leftIcons && paddingLeft,
          }}
          className="form-control"
          maxLength={max ? max : Infinity}
        />
      ) : (
        <NumericInput
          onChange={(value) => onChange(value)}
          value={value}
          onBlur={onBlur}
          inputRef={inputRef}
          placeholder={placeholder}
          id={id}
          min={min}
          max={max}
          allowEmpty={allowEmpty}
        />
      )}
      {typeof Icon === 'string' && (
        <img src={Icon} className="qe-input-icon-right" alt="input icon" />
      )}
      {Icon && typeof Icon !== 'string' && (
        <span className="qe-input-icon-right">{Icon}</span>
      )}
    </Form.Group>
  )
}
