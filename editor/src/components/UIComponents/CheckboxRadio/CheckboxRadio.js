import React, { useState } from 'react'
import { Form } from 'react-bootstrap'
import classNames from 'classnames'

import { useAppState } from '../../../hooks'
import { STATES } from '../../../helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from '../../TooltipContainer/TooltipContainer'

export const CheckboxRadio = ({
  id,
  labelText = '',
  options = [],
  className = '',
  optionClassName = '',
  dataTestId = '',
  onChange = () => {},
  update = () => {},
  activeDisabled = false,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  isCheckbox = true,
  groupName = '',
  value,
  hasReset = true,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const [optionsValues, setOptionsValues] = useState(value)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const disabled =
    (isSurveyActive && activeDisabled) ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled

  const toolTip =
    isSurveyActive && activeDisabled
      ? getTooltipMessages().ACTIVE_DISABLED
      : getTooltipMessages().NO_PERMISSION

  const handleOnChange = (selectedOption) => {
    const selectedOptionsValues = {
      ...optionsValues,
      [selectedOption.target.value]: selectedOption.target.checked,
    }
    setOptionsValues(selectedOptionsValues)
    onChange(selectedOptionsValues)
    update(selectedOptionsValues)
  }

  const handleReset = () => {
    let falsifiedOptions = {}
    Object.keys(optionsValues).forEach((key) => {
      falsifiedOptions[key] = false
    })
    setOptionsValues(falsifiedOptions)
    onChange(falsifiedOptions)
    update(falsifiedOptions)
  }

  return (
    <div
      className={classNames(`checkbox-radio-component ${className}`)}
      data-testid={dataTestId}
    >
      {labelText && <Form.Label htmlFor="select">{labelText}</Form.Label>}
      <TooltipContainer tip={toolTip} showTip={disabled}>
        <Form
          id={id}
          className={classNames(
            `checkbox-radio-component-form ${optionClassName}`
          )}
          onChange={handleOnChange}
        >
          {options.map((option, index) => (
            <Form.Check
              disabled={disabled}
              key={`${option.value}-${index}`}
              type={isCheckbox ? 'checkbox' : 'radio'}
              id={`${option.value}-${index}`}
              name={groupName}
              label={option.label}
              value={option.value}
              checked={value[option.value]}
            />
          ))}
          {hasReset && (
            <span className={'reset'} onClick={() => handleReset()}>
              {t('Reset')}
            </span>
          )}
        </Form>
      </TooltipContainer>
    </div>
  )
}
