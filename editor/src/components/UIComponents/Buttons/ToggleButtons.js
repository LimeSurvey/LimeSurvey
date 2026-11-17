import classNames from 'classnames'
import { Fragment, useContext, useEffect, useState } from 'react'
import { ToggleButton, ButtonGroup, Form } from 'react-bootstrap'

import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import {
  getOnOffOptions,
  getTooltipMessages,
  ONOFF_BOOLEAN,
} from 'helpers/options'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { QueryClientContext } from '@tanstack/react-query'

const getTwoConditions = () => getOnOffOptions(ONOFF_BOOLEAN)

const getThreeConditions = () => [
  { name: 'Yes', value: '1' },
  { name: 'Maybe', value: '0' },
  { name: 'No', value: '-1' },
]

// Only toggles that are locked without permission subscribe to the permission
// state, and only inside a QueryClientProvider, where the app state lives.
export const ToggleButtons = (props) => {
  const hasQueryClient = !!useContext(QueryClientContext)

  return props.noPermissionDisabled && hasQueryClient ? (
    <ToggleButtonsWithPermission {...props} />
  ) : (
    <ToggleButtonsField {...props} />
  )
}

const ToggleButtonsWithPermission = (props) => {
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  return (
    <ToggleButtonsField
      {...props}
      hasSurveyUpdatePermission={hasSurveyUpdatePermission}
    />
  )
}

// TODO this component will replace current ToggleButtons
const ToggleButtonsField = ({
  hasSurveyUpdatePermission = true,
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
  theme = 'light',
  noPermissionDisabled = false,
  noAccessDisabled = false,
  optionTextClassName = '',
  disabled,
  overlayMessage,
}) => {
  const [options, setOptions] = useState([])

  const isDisabled =
    disabled ||
    activeDisabled ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled
  const toolTip = overlayMessage
    ? overlayMessage
    : activeDisabled
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
        className={classNames('lime-toggle-btn-group', theme, {
          isSecondary,
        })}
      >
        {labelText && <Form.Label className="ui-label">{labelText}</Form.Label>}
        <ButtonGroup id={id}>
          {options.map((option, index) => (
            <Fragment key={`toggleButton-${id}-option-${index}`}>
              {option.control ?? (
                <ToggleButton
                  data-testid={`toggleButton-${id}-option-${index}`}
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
                        color={isChecked(option) ? '#515567' : '#6e748c'} // $g-750 and $g-700
                      />
                    )}
                    {option.name}
                  </div>
                </ToggleButton>
              )}
            </Fragment>
          ))}
        </ButtonGroup>
      </div>
    </TooltipContainer>
  )
}
