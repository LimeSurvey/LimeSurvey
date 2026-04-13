import classNames from 'classnames'
import { useEffect, useState } from 'react'
import { ToggleButton, ButtonGroup, Form } from 'react-bootstrap'

import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import {
  getOnOffOptions,
  getTooltipMessages,
  ONOFF_BOOLEAN,
} from 'helpers/options'
import { TooltipContainer } from 'components'
import { useQueryClient } from '@tanstack/react-query'

const getTwoConditions = () => getOnOffOptions(ONOFF_BOOLEAN)

const getThreeConditions = () => [
  { name: 'Yes', value: '1' },
  { name: 'Maybe', value: '0' },
  { name: 'No', value: '-1' },
]

// TODO this component will replace current ToggleButtons
export const ToggleButtons = ({
  id,
  toggleOptions,
  value = '',
  defaultValue,
  labelText,
  onOffToggle = true,
  name,
  height,
  isSecondary = false,
  onChange = () => {},
  update = () => {},
  activeDisabled = false,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  optionTextClassName = '',
  disabled,
  overlayMessage,
}) => {
  const [canUseAppState, setCanUseAppState] = useState(false)

  useEffect(() => {
    try {
      useQueryClient()
      setCanUseAppState(true)
    } catch (error) {
      setCanUseAppState(false)
    }
  }, [])

  const [options, setOptions] = useState([])
  const [isSurveyActive] = canUseAppState
    ? useAppState(STATES.IS_SURVEY_ACTIVE)
    : [true]
  const [hasSurveyUpdatePermission] = canUseAppState
    ? useAppState(STATES.HAS_SURVEY_UPDATE_PERMISSION)
    : [true]

  const isDisabled =
    disabled ||
    (isSurveyActive && activeDisabled) ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled
  const toolTip = overlayMessage
    ? overlayMessage
    : isSurveyActive && activeDisabled
      ? getTooltipMessages().ACTIVE_DISABLED
      : getTooltipMessages().NO_PERMISSION

  useEffect(() => {
    const translateOptions = (opts) => {
      return opts
    }

    if (toggleOptions) {
      setOptions(translateOptions(toggleOptions))
    } else {
      if (onOffToggle) {
        setOptions(translateOptions(getTwoConditions()))
      } else {
        setOptions(translateOptions(getThreeConditions()))
      }
    }
  }, [onOffToggle, toggleOptions])

  const handleChange = (option) => {
    onChange(option.value)
    // if option.value is string like "yes", we have to pass same value to API
    update(option.value)
  }

  const isChecked = (option) =>
    value === option.value ||
    (value === undefined && option.value === false) ||
    ((value === '' || value === undefined) && defaultValue === option.value)

  return (
    <TooltipContainer tip={toolTip} showTip={isDisabled}>
      <div
        className={classNames('lime-toggle-btn-group', {
          isSecondary,
        })}
      >
        {labelText && <Form.Label className="ui-label">{labelText}</Form.Label>}
        <ButtonGroup id={id}>
          {options.map((option, index) => (
            <ToggleButton
              data-testid={`toggleButton-${id}-option-${index}`}
              key={`toggleButton-${id}-option-${index}`}
              id={`toggleButton-${id}-option-${index}`}
              name={name || id || labelText}
              variant="outline-lime-toggle"
              value={option.value}
              checked={isChecked(option)}
              onClick={() => handleChange(option)}
              className="toggle-button"
              disabled={isDisabled || option.disabled}
              type="radio"
            >
              <div
                style={{ height: height && height }}
                className={`d-flex gap-2 justify-content-center align-items-center ${optionTextClassName}`}
              >
                {option.icon && (
                  <option.icon
                    size={24}
                    color={isChecked(option) ? '#6e748c' : 'white'}
                  />
                )}
                {option.name}
              </div>
            </ToggleButton>
          ))}
        </ButtonGroup>
      </div>
    </TooltipContainer>
  )
}
