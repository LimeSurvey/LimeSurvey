import ButtonGroup from 'react-bootstrap/ButtonGroup'
import ToggleButton from 'react-bootstrap/ToggleButton'
import { Form } from 'react-bootstrap'
import { useEffect, useState } from 'react'
import classNames from 'classnames'

const twoConditions = [
  { name: 'On', value: true },
  { name: 'Off', value: false },
]

const threeConditions = [
  { name: 'Yes', value: '1' },
  { name: 'Maybe', value: '0' },
  { name: 'No', value: '-1' },
]

// TODO this component will replace current ToggleButtons
export const ToggleButtons = ({
  id,
  toggleOptions,
  value,
  onChange = () => {},
  labelText,
  onOffToggle = true,
  name,
  height,
  isSecondary = false,
}) => {
  const [options, setOptions] = useState([])

  useEffect(() => {
    if (toggleOptions) {
      setOptions([...toggleOptions])
    } else {
      if (onOffToggle) {
        setOptions([...twoConditions])
      } else {
        setOptions([...threeConditions])
      }
    }
  }, [onOffToggle, toggleOptions])

  return (
    <div
      className={classNames('lime-toggle-btn-group', {
        isSecondary,
      })}
    >
      {labelText && <Form.Label>{labelText}</Form.Label>}
      <ButtonGroup id={id}>
        {options.map((option, idx) => (
          <ToggleButton
            data-testid={`${id}-option-${idx}`}
            key={idx}
            id={`${id}-option-${idx}`}
            type="radio"
            name={name}
            variant="outline-lime-toggle"
            value={option.value}
            checked={
              value === option.value ||
              (value === undefined && option.value === false)
            }
            onChange={() => onChange(option.value)}
          >
            <div
              style={{ height: height && height }}
              className="d-flex gap-2 justify-content-center align-items-center"
            >
              {option.icon && <option.icon height={20} width={20} />}
              {option.name}
            </div>
          </ToggleButton>
        ))}
      </ButtonGroup>
    </div>
  )
}
